<?php
namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use User\Service\AuthManager;
use User\Service\UserManager;
use User\Controller\PubTicketController;

class PubTicketControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        $authManager = $container->get(AuthManager::class);
        
        // Instantiate the controller and inject dependencies
        return new PubTicketController($entityManager, $userManager, $authManager);
    }
}