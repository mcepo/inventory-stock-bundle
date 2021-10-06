<?php

namespace SF9\InventoryStockBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;

use SF9\InventoryStockBundle\Message\OutOfStockNotification;
use SF9\InventoryStockBundle\Entity\Inventory;

use SF9\InventoryStockBundle\Pagination\Paginator;

class InventoryRepository extends ServiceEntityRepository
{
    /**
     * Messaging service used for sending out of stock notifications
     *
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(ManagerRegistry $registry, MessageBusInterface $bus)
    {
        $this->bus = $bus;
        parent::__construct($registry, Inventory::class);
    }

    /**
     * Method for storing multiple inventory records to database
     * used for performace in inventory import command
     * 
     * @param array $inventories array of Inventory::class objects
     * 
     * @return void
     */

    public function persistMany(array $inventories): void
    {
        foreach($inventories as $inventory)
        {
            $this->upsert($inventory);
        }

        $this->_em->flush();
    }

    /**
     * Method for storing single inventory in record into the database
     */

    public function persist(Inventory $inventory): void
    {
        $this->upsert($inventory);

        $this->_em->flush();
    }

    /**
     * Method for updateing a single inventory item in entityManager
     * detects if stock item is going out of stock and triggers out of stock notification
     */
    private function upsert(Inventory $inventory): void
    {
        $currentInventory = $this->findEqual($inventory);

        if(null !== $currentInventory)
        {
            $this->processIsGoingOutOfStock($currentInventory, $inventory);

            $currentInventory->setStock($inventory->getStock());
        } else {
            $this->_em->persist($inventory);
        }
    }

    private function processIsGoingOutOfStock(Inventory $current, Inventory $new): void
    {
        if(!$current->isOutOfStock() && $new->isOutOfStock())
        {
            $this->bus->dispatch(new OutOfStockNotification($new));
        }
    }

    /**
     * Returns all inventory records paginated for a defined page
     * 
     * @param int $page number of the page to return
     */
    public function getAll(int $page = 1): Paginator
    {
        $qb = $this->createQueryBuilder('i');

        return (new Paginator($qb))->paginate($page);
    }


    /**
     * Containes the conditions for inventory records to be considered equale
     * and tries to retrive an equale inventory item from the database under those 
     * conditions.
     * 
     * @param Inventory $inventory record that is being searched in storage
     * 
     * @return Inventory|null returns equale inventory record or null if non was found
     */
    public function findEqual(Inventory $inventory): ?Inventory
    {
        return $this->findOneBy(['sku' => $inventory->getSku(), 'branch' => $inventory->getBranch()]);
    }
}
