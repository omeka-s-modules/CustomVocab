<?php
namespace CustomVocab\Service;

use CustomVocab\DataType\CustomVocab;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;

class CustomVocabFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(
        ServiceLocatorInterface $serviceLocator, $name, $requestedName
    ) {
        return (bool) preg_match('/^customvocab:\d+$/', $name);
    }

    public function createServiceWithName(
        ServiceLocatorInterface $serviceLocator, $name, $requestedName
    ) {
        // Derive the custom vocab ID, fetch the representation, and pass it to
        // the data type.
        $id = (int) substr($name, strrpos($name, ':') + 1);
        $vocab = $serviceLocator->getServiceLocator()->get('Omeka\ApiManager')
            ->read('custom_vocabs', $id)->getContent();
        return new CustomVocab($vocab);
    }
}
