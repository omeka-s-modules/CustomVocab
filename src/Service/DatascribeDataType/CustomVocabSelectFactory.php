<?php
namespace CustomVocab\Service\DatascribeDataType;

use CustomVocab\DatascribeDataType\CustomVocabSelect;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CustomVocabSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new CustomVocabSelect($services->get('Omeka\ApiManager'));
    }
}
