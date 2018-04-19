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
            'name' => 'o:terms',
            'type' => 'textarea',
            'options' => [
                'label' => 'Terms', // @translate
                'info' => 'All terms in this vocabulary, separated by new lines.', // @translate
            ],
            'attributes' => [
                'required' => true,
                'rows' => 20,
                'id' => 'o-terms',
            ],
        ]);
    }
}
