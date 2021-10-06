<?php

namespace SF9\InventoryStockBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class InventoryStockExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $outOfStockNotificationdef = $container->getDefinition('SF9\InventoryStockBundle\MessageHandler\OutOfStockNotificationHandler');
        $outOfStockNotificationdef->setArgument(0,  $config['out_of_stock_email']['to']);
        $outOfStockNotificationdef->setArgument(1,  $config['out_of_stock_email']['from']);

        $importStockCommanddef = $container->getDefinition('SF9\InventoryStockBundle\Command\ImportStockCommand');
        $importStockCommanddef->setArgument(0,  $config['csv_path']);
    }
}