<?php
namespace CustomVocab\Form;

use Omeka\Form\AbstractForm;

class CustomVocabForm extends AbstractForm
{
    public function buildForm()
    {
        $translator = $this->getTranslator();

        $this->add([
            'name' => 'o:label',
            'type' => 'text',
            'options' => [
                'label' => $translator->translate('Label'),
                'info' => $translator->translate('A human-readable title of the custom vocabulary.'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:lang',
            'type' => 'text',
            'options' => [
                'label' => $translator->translate('Language'),
                'info' => $translator->translate('The language of the vocabulary terms.'),
            ],
        ]);

        $this->add([
            'name' => 'o:terms',
            'type' => 'textarea',
            'options' => [
                'label' => $translator->translate('Terms'),
                'info' => $translator->translate('All terms in this vocabulary, separated by new lines.'),
            ],
            'attributes' => [
                'required' => true,
                'rows' => 20,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type'  => 'Submit',
            'attributes' => [
                'value' => $translator->translate('Submit'),
            ],
        ]);
    }
}
