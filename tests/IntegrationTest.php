<?php
namespace SF9\InventoryStockBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntegrationTest extends KernelTestCase
{
    public function testWiring()
    {
        self::bootKernel();
    }
}