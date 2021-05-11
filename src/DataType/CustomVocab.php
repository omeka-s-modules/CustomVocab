<?php
namespace CustomVocab\DataType;

use CustomVocab\Api\Representation\CustomVocabRepresentation;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\DataType\AbstractDataType;
use Omeka\Entity\Value;
use Laminas\Form\Element\Select;
use Laminas\View\Renderer\PhpRenderer;

class CustomVocab extends AbstractDataType
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
        $itemSet = $this->vocab->itemSet();
        if ($itemSet) {
            // Get items by item type and use as value options.
            $response = $view->api()->search('items', [
                'item_set_id' => $itemSet->id(),
                'sort_by' => 'title',
            ]);
            $items = $response->getContent();
            $valueOptions = [];
            foreach ($items as $item) {
                $valueOptions[$item->id()] = sprintf(
                    $view->translate('%s (#%s)'),
                    $item->displayTitle(),
                    $item->id()
                );
            }
            $select = new Select('customvocab');
            $select->setAttribute('data-value-key', 'value_resource_id')
                ->setAttribute('class', 'terms to-require')
                ->setEmptyOption($view->translate('Select item below'))
                ->setValueOptions($valueOptions);
            return $view->formSelect($select);
        }

        if ($this->vocab->uris()) {
            $uris = array_map('trim', preg_split("/\r\n|\n|\r/", $this->vocab->uris()));
            $valueOptions = [];
            foreach ($uris as $uri) {
                if (preg_match('/^(\S+) (.+)$/', $uri, $matches)) {
                    $uri = $matches[1];
                    $label = $matches[2];
                    $valueOptions[] = [
                        'value' => $uri,
                        'label' => sprintf($view->translate('%s <%s>'), $label, $uri),
                        'attributes' => [
                            'data-label' => $label,
                        ],
                    ];
                } elseif (preg_match('/^(.+)/', $uri, $matches)) {
                    $uri = $matches[1];
                    $valueOptions[] = [
                        'value' => $uri,
                        'label' => $uri,
                    ];
                }
            }
            $select = new Select('customvocab');
            $select->setAttribute('data-value-key', '@id')
                ->setAttribute('class', 'terms to-require custom-vocab-uri')
                ->setEmptyOption($view->translate('Select URI below'))
                ->setValueOptions($valueOptions);
            return $view->formSelect($select);
        }

        $terms = array_map('trim', preg_split("/\r\n|\n|\r/", $this->vocab->terms()));
        $valueOptions = array_combine($terms, $terms);
        $select = new Select('customvocab');
        $select->setAttribute('data-value-key', '@value')
            ->setAttribute('class', 'terms to-require')
            ->setEmptyOption($view->translate('Select term below'))
            ->setValueOptions($valueOptions);
        return $view->formSelect($select);
    }

    public function isValid(array $valueObject)
    {
        if (isset($valueObject['value_resource_id'])
            && is_numeric($valueObject['value_resource_id'])
        ) {
            return true;
        } elseif ($valueObject['@id']
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
        } elseif ($valueObject['@id']
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
        $dataType = $adapter->getServiceLocator()
            ->get('Omeka\DataTypeManager')
            ->get($dataTypeName)
            ->hydrate($valueObject, $value, $adapter);
    }

    public function render(PhpRenderer $view, ValueRepresentation $value)
    {
        $valueResource = $value->valueResource();
        if ($valueResource) {
            return $valueResource->linkPretty();
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
}
