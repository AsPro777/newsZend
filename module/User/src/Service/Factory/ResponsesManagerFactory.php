<?php

namespace User\Service\Factory;

use Interop\Container\ContainerInterface;
use User\Service\ResponsesManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResponsesManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        // Instantiate the service and inject dependencies
        return new ResponsesManager($entityManager);
    }
}