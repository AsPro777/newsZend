<?php
namespace User\Service\Factory;

use Interop\Container\ContainerInterface;
use User\Service\DrvFormManager;
use Zend\Authentication\AuthenticationService;

class DrvFormManagerFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authenticationService = $container->get(AuthenticationService::class);        
        return new DrvFormManager($authenticationService, $entityManager);
    }
}
