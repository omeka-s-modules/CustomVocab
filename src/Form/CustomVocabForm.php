<?php
namespace CustomVocab\Form;

use Laminas\Form\Form;

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
            'name' => 'vocab_type',
            'type' => 'radio',
            'options' => [
                'label' => 'Vocab type',
                'value_options' => [
                    'literal' => 'Text', // @translate
                    'resource' => 'Resource', // @translate
                    'uri' => 'URI', // @translate
                ],
            ],
            'attributes' => [
                'class' => 'vocab-type',
            ],
        ]);

        $this->add([
            'name' => 'o:item_set',
            'type' => 'Omeka\Form\Element\ItemSetSelect',
            'options' => [
                'label' => 'Items', // @translate
                'info' => 'Enter the item set containing the items in this vocabulary.', // @translate
                'empty_option' => 'Select an item set',
            ],
            'attributes' => [
                'id' => 'o-item-set',
            ],
        ]);

        $this->add([
            'name' => 'o:terms',
            'type' => 'textarea',
            'options' => [
                'label' => 'Terms', // @translate
                'info' => 'Enter all the terms in this vocabulary, separated by new lines.', // @translate
            ],
            'attributes' => [
                'rows' => 20,
                'id' => 'o-terms',
            ],
        ]);

        $this->add([
            'name' => 'o:uris',
            'type' => 'textarea',
            'options' => [
                'label' => 'URIs', // @translate
                'info' => 'Enter all the URIs in this vocabulary, separated by new lines. You may label a URI by including the label after the URI, separated by a space.', // @translate
            ],
            'attributes' => [
                'rows' => 20,
                'id' => 'o-uris',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o:item_set',
            'allow_empty' => true,
        ]);
    }
}
