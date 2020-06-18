<?php
namespace CustomVocab\Service;

use CustomVocab\DataType\CustomVocab;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class CustomVocabFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $services, $requestedName)
    {
        return (bool) preg_match('/^customvocab:\d+$/', $requestedName);
    }

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        // Derive the custom vocab ID, fetch the representation, and pass it to
        // the data type.
        $id = (int) substr($requestedName, strrpos($requestedName, ':') + 1);
        $vocab = $services->get('Omeka\ApiManager')
            ->read('custom_vocabs', $id)->getContent();
        return new CustomVocab($vocab);
    }
}
