<?php

namespace User\Service\Factory;

use User\Service\UserManager;
use User\Service\TicketManager;
use User\Service\CharterPaxManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TicketManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        $charterPaxManager = $container->get(CharterPaxManager::class);

        // Instantiate the service and inject dependencies
        return new TicketManager($entityManager, $userManager, $charterPaxManager);
    }
}
