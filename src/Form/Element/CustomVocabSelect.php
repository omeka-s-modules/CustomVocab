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

    /**
     * @see https://github.com/zendframework/zendframework/issues/2761#issuecomment-14488216
     *
     * {@inheritDoc}
     * @see \Laminas\Form\Element\Select::getInputSpecification()
     */
    public function getInputSpecification()
    {
        $inputSpecification = parent::getInputSpecification();
        $inputSpecification['required'] = isset($this->attributes['required'])
            && $this->attributes['required'];
        return $inputSpecification;
    }

    public function getValueOptions()
    {
        $customVocabId = $this->getOption('custom_vocab_id');

        try {
            /** @var \CustomVocab\Api\Representation\CustomVocabRepresentation $customVocab */
            $customVocab = $this->api->read('custom_vocabs', $customVocabId)->getContent();
        } catch (\Omeka\Api\Exception\NotFoundException $e) {
            return [];
        }

        $appendIdToTitle = (bool) ($this->getOption('append_id_to_title') ?? true);
        return $customVocab->listValues($appendIdToTitle);
    }

    public function setApiManager(ApiManager $api): self
    {
        $this->api = $api;
        return $this;
    }
}
