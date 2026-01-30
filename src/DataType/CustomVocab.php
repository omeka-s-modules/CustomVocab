<?php
namespace CustomVocab\DataType;

use CustomVocab\Api\Representation\CustomVocabRepresentation;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\DataType\DataTypeWithOptionsInterface;
use Omeka\DataType\ConversionTargetInterface;
use Omeka\DataType\ValueAnnotatingInterface;
use Omeka\Entity\Value;
use Laminas\View\Renderer\PhpRenderer;

class CustomVocab implements DataTypeWithOptionsInterface, ValueAnnotatingInterface, ConversionTargetInterface
{
    /**
     * @var CustomVocabRepresentation
     */
    protected $vocab;

    /**
     * Constructor
     *
     * @param CustomVocabRepresentation $vocab
     */
    public function __construct(CustomVocabRepresentation $vocab)
    {
        $this->vocab = $vocab;
    }

    public function getName()
    {
        return 'customvocab:' . $this->vocab->id();
    }

    public function getOptgroupLabel()
    {
        return 'Custom Vocab'; // @translate
    }

    public function getLabel()
    {
        return $this->vocab->label();
    }

    public function prepareForm(PhpRenderer $view)
    {
        $view->headScript()->appendFile($view->assetUrl('js/resource-form.js', 'CustomVocab'));
    }

    public function form(PhpRenderer $view)
    {
        switch ($this->vocab->type()) {
            case 'resource':
                return $this->getResourceForm($view);
            case 'uri':
                return $this->getUriForm($view);
            case 'literal':
            default:
                return $this->getLiteralForm($view);
        }
    }

    /**
     * Get the form for the resource type.
     *
     * @param PhpRenderer $view
     * @return string
     */
    protected function getResourceForm(PhpRenderer $view)
    {
        $select = $this->vocab->select(['append_id_to_title' => true]);
        $select
            ->setName('customvocab')
            ->setAttribute('data-value-key', 'value_resource_id')
            ->setAttribute('class', 'custom-vocab-resource to-require')
            ->setAttribute('data-placeholder', $view->translate('Select item below'))
            ->setEmptyOption('');
        return $view->formSelect($select);
    }

    /**
     * Get the form for the URI type.
     *
     * @param PhpRenderer $view
     * @return string
     */
    protected function getUriForm(PhpRenderer $view)
    {
        $select = $this->vocab->select(['append_uri_to_label' => true]);
        $select
            ->setName('customvocab')
            ->setAttribute('data-value-key', '@id')
            ->setAttribute('class', 'custom-vocab-uri to-require')
            ->setAttribute('data-placeholder', $view->translate('Select URI below'))
            ->setEmptyOption('');
        return $view->formSelect($select);
    }

    /**
     * Get the form for the literal type.
     *
     * @param PhpRenderer $view
     * @return string
     */
    protected function getLiteralForm(PhpRenderer $view)
    {
        $select = $this->vocab->select();
        $select
            ->setName('customvocab')
            ->setAttribute('data-value-key', '@value')
            ->setAttribute('class', 'custom-vocab-literal to-require')
            ->setAttribute('data-placeholder', $view->translate('Select term below'))
            ->setEmptyOption('');
        return $view->formSelect($select);
    }

    public function isValid(array $valueObject)
    {
        if (isset($valueObject['value_resource_id'])
            && is_numeric($valueObject['value_resource_id'])
        ) {
            return true;
        } elseif (isset($valueObject['@id'])
            && is_string($valueObject['@id'])
            && '' !== trim($valueObject['@id'])
        ) {
            return true;
        } elseif (isset($valueObject['@value'])
            && is_string($valueObject['@value'])
            && '' !== trim($valueObject['@value'])
        ) {
            return true;
        }
        return false;
    }

    public function hydrate(array $valueObject, Value $value, AbstractEntityAdapter $adapter)
    {
        if (isset($valueObject['value_resource_id'])
            && is_numeric($valueObject['value_resource_id'])
        ) {
            $dataTypeName = 'resource:item';
        } elseif (isset($valueObject['@id'])
            && is_string($valueObject['@id'])
            && '' !== trim($valueObject['@id'])
        ) {
            $dataTypeName = 'uri';
            $valueObject['@language'] = $this->vocab->lang();
            $valueObject['o:label'] = $this->vocab->listUriLabels()[$valueObject['@id']] ?? null;
        } elseif (isset($valueObject['@value'])
            && is_string($valueObject['@value'])
            && '' !== trim($valueObject['@value'])
        ) {
            $dataTypeName = 'literal';
            $valueObject['@language'] = $this->vocab->lang();
        }
        $adapter->getServiceLocator()
            ->get('Omeka\DataTypeManager')
            ->get($dataTypeName)
            ->hydrate($valueObject, $value, $adapter);
    }

    public function render(PhpRenderer $view, ValueRepresentation $value, $options = [])
    {
        $lang = $options['lang'] ?? null;
        $valueResource = $value->valueResource();
        if ($valueResource) {
            return $valueResource->linkPretty('square', null, null, null, $lang);
        }
        if ($value->uri()) {
            $uri = $value->uri();
            $uriLabel = $value->value();
            if (!$uriLabel) {
                $uriLabel = $uri;
            }
            return $view->hyperlink($uriLabel, $uri, ['target' => '_blank']);
        }
        return nl2br($view->escapeHtml($value->value()));
    }

    public function getJsonLd(ValueRepresentation $value)
    {
        $valueResource = $value->valueResource();
        if ($valueResource) {
            return $valueResource->valueRepresentation();
        }
        if ($value->uri()) {
            $jsonLd = ['@id' => $value->uri()];
            if ($value->value()) {
                $jsonLd['o:label'] = $value->value();
            }
            return $jsonLd;
        }
        $jsonLd = ['@value' => $value->value()];
        if ($value->lang()) {
            $jsonLd['@language'] = $value->lang();
        }
        return $jsonLd;
    }

    public function getFulltextText(PhpRenderer $view, ValueRepresentation $value)
    {
        return $value->value();
    }

    public function toString(ValueRepresentation $value)
    {
        $valueResource = $value->valueResource();
        if ($valueResource) {
            return $valueResource->url(null, true);
        }
        return (string) $value->value();
    }

    public function valueAnnotationPrepareForm(PhpRenderer $view)
    {
    }

    public function valueAnnotationForm(PhpRenderer $view)
    {
        return $this->form($view);
    }

    public function convert(Value $valueObject, string $dataTypeTarget): bool
    {
        switch ($this->vocab->type()) {
            case 'resource':
                // Does not support convert to custom vocab (resource) due to
                // the potentially massive overhead required to determine
                // whether the item is assigned to the vocab's item set.
                break;
            case 'uri':
                // Attempt to convert to custom vocab (uri).
                $value = $valueObject->getValue();
                $uri = $valueObject->getUri();
                $uris = $this->vocab->uris();
                if (is_string($uri) && '' !== trim($uri) && array_key_exists($uri, $uris)) {
                    if (is_string($uris[$uri]) && '' !== trim($uris[$uri])) {
                        // If the URI has a label, set it as the value. This will
                        // overwrite any existing value, and therefore data loss
                        // may occur. Even so, this behavior is desirable because
                        // we're converting to a controlled vocabulary.
                        $valueObject->setValue($uris[$uri]);
                    }
                    return true;
                }
                break;
            case 'literal':
                // Attempt to convert to custom vocab (literal).
                $value = $valueObject->getValue();
                $terms = $this->vocab->terms();
                if (is_string($value) && '' !== trim($value) && in_array($value, $terms)) {
                    return true;
                }
                break;
        }
        return false;
    }
}
