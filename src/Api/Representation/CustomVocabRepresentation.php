<?php
namespace CustomVocab\Api\Representation;

use CustomVocab\Form\Element\CustomVocabSelect;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\ItemSetRepresentation;
use Omeka\Api\Representation\UserRepresentation;

class CustomVocabRepresentation extends AbstractEntityRepresentation
{
    public function getControllerName()
    {
        return 'custom-vocab';
    }

    public function getJsonLdType()
    {
        return 'o:CustomVocab';
    }

    public function getJsonLd()
    {
        $itemSet = null;
        $owner = null;
        if ($this->itemSet()) {
            $itemSet = $this->itemSet()->getReference();
        }
        if ($this->owner()) {
            $owner = $this->owner()->getReference();
        }
        return [
            'o:label' => $this->label(),
            'o:lang' => $this->lang(),
            'o:terms' => $this->terms(),
            'o:uris' => $this->uris(),
            'o:item_set' => $itemSet,
            'o:owner' => $owner,
        ];
    }

    public function label(): string
    {
        return $this->resource->getLabel();
    }

    public function lang(): ?string
    {
        return $this->resource->getLang();
    }

    public function itemSet(): ?ItemSetRepresentation
    {
        return $this->getAdapter('item_sets')
            ->getRepresentation($this->resource->getItemSet());
    }

    public function terms()
    {
        return $this->resource->getTerms();
    }

    public function uris()
    {
        return $this->resource->getUris();
    }

    /**
     * The type of values can be "resource", "uri", "literal" or null (unknown).
     */
    public function typeValues(): ?string
    {
        // Normally, values are checked in adapter on save, so no more check.
        if ($this->resource->getItemSet()) {
            return 'resource';
        } elseif ($this->resource->getUris()) {
            return 'uri';
        } elseif ($this->resource->getTerms()) {
            return 'literal';
        } else {
            return null;
        }
    }

    /**
     * List values as value/label, whatever the type.
     */
    public function listValues(array $options = []): array
    {
        switch ($this->typeValues()) {
            case 'resource':
                return $this->listItemTitles($options) ?? [];
            case 'uri':
                return $this->listUriLabels() ?? [];
            case 'literal':
                return $this->listTerms() ?? [];
            default:
                return [];
        }
    }

    /**
     * List item titles by id when the vocab is based on an item set.
     */
    public function listItemTitles(array $options = []): ?array
    {
        $itemSet = $this->resource->getItemSet();
        if (!$itemSet) {
            return null;
        }
        $result = [];
        /** @var \Omeka\Api\Representation\ItemRepresentation[] $items */
        $items = $this->getServiceLocator()->get('Omeka\ApiManager')
            ->search('items', ['item_set_id' => $itemSet->getId()])
            ->getContent();
        $lang = $this->lang();
        if (!empty($options['append_id_to_title'])) {
            $label = $this->getTranslator()->translate('%s (#%s)'); // @translate
            foreach ($items as $item) {
                $itemId = $item->id();
                $result[$itemId] = sprintf($label, $item->displayTitle(null, $lang), $itemId);
            }
        } else {
            foreach ($items as $item) {
                $result[$item->id()] = $item->displayTitle(null, $lang);
            }
        }
        natcasesort($result);
        return $result;
    }

    /**
     * List of terms by term when the vocab is a simple list.
     */
    public function listTerms(): ?array
    {
        $terms = trim($this->resource->getTerms());
        if (!strlen($terms)) {
            return null;
        }
        $terms = array_filter(array_map('trim', explode("\n", $terms)), 'strlen') ?: null;
        return $terms
            ? array_combine($terms, $terms)
            : null;
    }

    /**
     * List of uris (as key) and labels when the vocab is a list of uris.
     */
    public function listUriLabels(): ?array
    {
        $uris = trim($this->resource->getUris());
        if (!strlen($uris)) {
            return null;
        }
        $result = [];
        $matches = [];
        foreach (array_filter(array_map('trim', explode("\n", $uris)), 'strlen') as $uri) {
            if (preg_match('/^(\S+) (.+)$/', $uri, $matches)) {
                $result[$matches[1]] = $matches[2];
            } elseif (preg_match('/^(.+)/', $uri, $matches)) {
                $result[$matches[1]] = $matches[1];
            }
        }
        return $result ?: null;
    }

    public function owner(): ?UserRepresentation
    {
        return $this->getAdapter('users')
            ->getRepresentation($this->resource->getOwner());
    }

    public function select(array $options = []): CustomVocabSelect
    {
        $options['custom_vocab_id'] = $this->id();
        return $this->getServiceLocator()->get('FormElementManager')
            ->get(CustomVocabSelect::class)
            ->setOptions($options);
    }
}
