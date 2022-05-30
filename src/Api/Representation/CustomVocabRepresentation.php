<?php
namespace CustomVocab\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

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

    public function itemSet(): ?\Omeka\Api\Representation\ItemSetRepresentation
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
     * List of terms when the vocab is a simple list.
     */
    public function listTerms(): ?array
    {
        $terms = trim($this->resource->getTerms());
        if (!strlen($terms)) {
            return null;
        }
        return array_filter(array_map('trim', explode("\n", $terms)), 'strlen') ?: null;
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

    public function owner(): ?\Omeka\Api\Representation\UserRepresentation
    {
        return $this->getAdapter('users')
            ->getRepresentation($this->resource->getOwner());
    }
}
