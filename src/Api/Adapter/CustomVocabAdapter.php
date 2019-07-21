<?php
namespace CustomVocab\Api\Adapter;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class CustomVocabAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return 'custom_vocabs';
    }

    public function getRepresentationClass()
    {
        return \CustomVocab\Api\Representation\CustomVocabRepresentation::class;
    }

    public function getEntityClass()
    {
        return \CustomVocab\Entity\CustomVocab::class;
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $this->hydrateOwner($request, $entity);
        if ($this->shouldHydrate($request, 'o:label')) {
            $entity->setLabel($request->getValue('o:label'));
        }
        if ($this->shouldHydrate($request, 'o:lang')) {
            $entity->setLang($request->getValue('o:lang'));
        }
        if ($this->shouldHydrate($request, 'o:terms')) {
            $entity->setTerms($this->sanitizeTerms($request->getValue('o:terms')));
        }
    }

    public function validateEntity(EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $label = $entity->getLabel();
        if (false == trim($label)) {
            $errorStore->addError('o:label', 'The label cannot be empty.'); // @translate
        }
        if (!$this->isUnique($entity, ['label' => $label])) {
            $errorStore->addError('o:label', 'The label is already taken.'); // @translate
        }

        if (false == trim($entity->getTerms())) {
            $errorStore->addError('o:terms', 'The terms cannot be empty.'); // @translate
        }
    }

    protected function sanitizeTerms($terms)
    {
        // The str_replace() allows to fix Apple copy/paste.
        $terms = explode("\n", str_replace(["\r\n", "\n\r", "\r"], ["\n", "\n", "\n"], $terms)); // explode at end of line
        $terms = array_map('trim', $terms); // trim all terms
        $terms = array_filter($terms); // remove empty terms
        $terms = array_unique($terms); // remove duplicate terms
        return trim(implode("\n", $terms));
    }
}
