<?php

namespace CustomVocab\Service\Stdlib;

use CustomVocab\Stdlib\ImportExport;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ImportExportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new ImportExport($services->get('Omeka\ApiManager'));
    }
}
