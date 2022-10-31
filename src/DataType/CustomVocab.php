<?php
namespace CustomVocab\DataType;

use CustomVocab\Api\Representation\CustomVocabRepresentation;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\DataType\AbstractDataType;
use Omeka\DataType\ValueAnnotatingInterface;
use Omeka\Entity\Value;
use Laminas\Form\Element\Select;
use Laminas\View\Renderer\PhpRenderer;

class CustomVocab extends AbstractDataType implements ValueAnnotatingInterface
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

    public function form(PhpRenderer $view)
    {
        switch ($this->vocab->typeValues()) {
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
            ->setAttribute('class', 'terms to-require')
            ->setEmptyOption($view->translate('Select item below'));
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
        $valueOptions = [];
        $uriLabels = $this->vocab->listUriLabels() ?? [];
        $sLabel = $view->translate('%s <%s>'); // @translate
        foreach ($uriLabels as $uri => $label) {
            if ($uri !== $label) {
                $valueOptions[] = [
                    'value' => $uri,
                    'label' => sprintf($sLabel, $label, $uri),
                    'attributes' => [
                        'data-label' => $label,
                    ],
                ];
            } else {
                $valueOptions[] = [
                    'value' => $uri,
                    'label' => $uri,
                ];
            }
        }
        $select = $this->vocab->select();
        $select
            ->setName('customvocab')
            ->setAttribute('data-value-key', '@id')
            ->setAttribute('class', 'terms to-require custom-vocab-uri')
            ->setEmptyOption($view->translate('Select URI below'))
            ->setValueOptions($valueOptions);
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
            ->setAttribute('class', 'terms to-require')
            ->setEmptyOption($view->translate('Select term below'));
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

    public function render(PhpRenderer $view, ValueRepresentation $value, $lang = null)
    {
        $valueResource = $value->valueResource();
        if ($valueResource) {
            return $valueResource->linkPretty('square');
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
}
