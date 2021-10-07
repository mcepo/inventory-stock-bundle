<?php
namespace SF9\InventoryStockBundle\Tests;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use SF9\InventoryStockBundle\InventoryStockBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestingKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TwigBundle(),
            new DAMADoctrineTestBundle(),
            new InventoryStockBundle()
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../src/Resources/config/services.xml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../src/Resources/config/routes.xml');
    }
}
