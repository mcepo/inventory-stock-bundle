<?php
namespace SF9\InventoryStockBundle\Message;

use SF9\InventoryStockBundle\Entity\Inventory;

class OutOfStockNotification
{
    private $content;

    public function __construct(Inventory $inventory)
    {
        $this->content = $inventory->getSku() . " is out of stock at location " . $inventory->getBranch();
    }

    public function getContent(): string
    {
        return $this->content;
    }
}