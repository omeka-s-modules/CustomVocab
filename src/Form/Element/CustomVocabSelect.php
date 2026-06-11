<?php declare(strict_types=1);

namespace CustomVocab\Form\Element;

use Laminas\Form\Element\Select;
use Omeka\Api\Manager as ApiManager;

class CustomVocabSelect extends Select
{
    protected $attributes = [
        'type' => 'select',
        'multiple' => false,
        'class' => 'chosen-select',
    ];

    /**
     * @var ApiManager
     */
    protected $api;

    public function getInputSpecification(): array
    {
        $inputSpecification = parent::getInputSpecification();
        $inputSpecification['required'] = isset($this->attributes['required']) && $this->attributes['required'];
        return $inputSpecification;
    }

    public function getValueOptions(): array
    {
        /**
         * @var \CustomVocab\Api\Representation\CustomVocabRepresentation $customVocab
         */

        static $computeds = [];

        ksort($this->options);
        $hash = md5(serialize($this->options));
        if (isset($computeds[$hash])) {
            return $computeds[$hash];
        }

        $computeds[$hash] = [];

        $customVocabId = $this->getOption('custom_vocab_id');

        try {
            $customVocab = $this->api->read('custom_vocabs', $customVocabId)->getContent();
        } catch (\Omeka\Api\Exception\NotFoundException $e) {
            return [];
        }

        $valueOptions = $customVocab->listValues($this->getOptions());

        $prependValueOptions = $this->getOption('prepend_value_options');
        if (is_array($prependValueOptions)) {
            $valueOptions = $prependValueOptions + $valueOptions;
        }

        $computeds[$hash] = $valueOptions;

        $this->setValueOptions($valueOptions);
        return $valueOptions;
    }

    public function setApiManager(ApiManager $api): self
    {
        $this->api = $api;
        return $this;
    }
}
