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
        $select = new Select('customvocab');
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
            $select->setAttribute('data-value-key', 'value_resource_id')
                ->setEmptyOption($view->translate('Select item below'));
        } else {
            // Normalize vocab terms for use in a select element.
            $terms = array_map('trim', explode(PHP_EOL, $this->vocab->terms()));
            $valueOptions = array_combine($terms, $terms);
            $select->setAttribute('data-value-key', '@value')
                ->setEmptyOption($view->translate('Select term below'));
        }
        $select->setValueOptions($valueOptions)
            ->setAttribute('class', 'terms to-require');

        return $view->formSelect($select);
    }

    public function isValid(array $valueObject)
    {
        if (isset($valueObject['value_resource_id'])
            && is_numeric($valueObject['value_resource_id'])
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
        return nl2br($view->escapeHtml($value->value()));
    }

    public function getJsonLd(ValueRepresentation $value)
    {
        $valueResource = $value->valueResource();
        if ($valueResource) {
            return $valueResource->valueRepresentation();
        }
        $jsonLd = ['@value' => $value->value()];
        if ($value->lang()) {
            $jsonLd['@language'] = $value->lang();
        }
        return $jsonLd;
    }
}
