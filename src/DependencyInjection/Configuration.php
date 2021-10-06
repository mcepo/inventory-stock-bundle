<?php

namespace SF9\InventoryStockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('inventory_stock');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('csv_path')
                    ->defaultNull()
                    ->info('Path relative to project root to csv file containing inventory stock data to be imported.')
                ->end()
                ->scalarNode('out_of_stock_email_to')
                    ->defaultNull()
                    ->info('Email address where to send the out of stock notification. Required in order for notifications to work.')
                ->end()
                ->scalarNode('out_of_stock_email_from')
                    ->defaultNull()
                    ->info('Email address from where the out of stock notification was sent. Required in order for notifications to work.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}