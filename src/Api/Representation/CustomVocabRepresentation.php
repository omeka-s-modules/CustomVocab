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
        $owner = null;
        if ($this->owner()) {
            $owner = $this->owner()->getReference();
        }
        return [
            'o:label' => $this->label(),
            'o:lang' => $this->lang(),
            'o:terms' => $this->terms(),
            'o:owner' => $owner,
        ];
    }

    public function label()
    {
        return $this->resource->getLabel();
    }

    public function lang()
    {
        return $this->resource->getLang();
    }

    public function terms()
    {
        return $this->resource->getTerms();
    }

    public function owner()
    {
        return $this->getAdapter('users')
            ->getRepresentation($this->resource->getOwner());
    }
}
