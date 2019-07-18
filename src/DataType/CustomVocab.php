<?php
namespace CustomVocab\DataType;

use CustomVocab\Api\Representation\CustomVocabRepresentation;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\DataType\Literal;
use Omeka\Entity\Value;
use Zend\Form\Element\Select;
use Zend\View\Renderer\PhpRenderer;

class CustomVocab extends Literal
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
        // Normalize vocab terms for use in a select element.
        $terms = array_map('trim', explode(PHP_EOL, $this->vocab->terms()));
        $valueOptions = array_combine($terms, $terms);

        $select = new Select('customvocab');
        $select->setAttributes([
                'class' => 'terms to-require',
                'data-value-key' => '@value',
            ])
            ->setEmptyOption('Select below') // @translate
            ->setValueOptions($valueOptions);

        return $view->formSelect($select);
    }

    public function hydrate(array $valueObject, Value $value, AbstractEntityAdapter $adapter)
    {
        $valueObject['@language'] = $this->vocab->lang();
        parent::hydrate($valueObject, $value, $adapter);
    }
}
