<?php
namespace CustomVocab\Form;

use Zend\Form\Form;

class CustomVocabForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o:label',
            'type' => 'text',
            'options' => [
                'label' => 'Label', // @translate
                'info' => 'A human-readable title of the custom vocabulary.', // @translate
            ],
            'attributes' => [
                'required' => true,
                'id' => 'o-label',
            ],
        ]);

        $this->add([
            'name' => 'o:lang',
            'type' => 'text',
            'options' => [
                'label' => 'Language', // @translate
                'info' => 'The language of the vocabulary terms.', // @translate
            ],
            'attributes' => [
                'id' => 'o-lang',
            ],
        ]);

        $this->add([
            'name' => 'o:item_set',
            'type' => 'Omeka\Form\Element\ItemSetSelect',
            'options' => [
                'label' => 'Items', // @translate
                'info' => 'Enter the item set containing the items in this vocabulary.', // @translate
                'empty_option' => '',
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select an item set', // @translate
            ],
        ]);

        $this->add([
            'name' => 'o:terms',
            'type' => 'textarea',
            'options' => [
                'label' => 'Terms', // @translate
                'info' => 'Enter all the terms in this vocabulary, separated by new lines. This will be ignored if an item set is selected above.', // @translate
            ],
            'attributes' => [
                'rows' => 20,
                'id' => 'o-terms',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o:item_set',
            'allow_empty' => true,
        ]);
    }
}
