<?php
namespace User\Controller\Factory;

use User\Service\UserManager;
use User\Service\ZakazManager;
use User\Service\TicketManager;
use User\Controller\PubZakazController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PubZakazControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $zakazManager = $container->get(ZakazManager::class);
        $userManager  = $container->get(UserManager::class);
        $ticketManager = $container->get(TicketManager::class);

        return new PubZakazController($userManager, $zakazManager, $ticketManager);
    }
}
