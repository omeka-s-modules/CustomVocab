<?php

namespace CustomVocab\Service\Controller\Admin;

use CustomVocab\Controller\Admin\IndexController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new IndexController($services->get('CustomVocab\ImportExport'));
    }
}
