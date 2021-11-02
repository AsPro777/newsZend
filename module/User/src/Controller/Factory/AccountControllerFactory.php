<?php
namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use User\Service\UserManager;
use Application\Service\ImageManager;

class AccountControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        $imageManager = $container->get(ImageManager::class);
        // Instantiate the controller and inject dependencies
        return new \User\Controller\AccountController($entityManager, $userManager, $imageManager);
    }
}