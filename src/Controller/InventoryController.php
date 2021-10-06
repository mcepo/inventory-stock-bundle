<?php

namespace SF9\InventoryStockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use SF9\InventoryStockBundle\Entity\Inventory;
use SF9\InventoryStockBundle\Repository\InventoryRepository;
use SF9\InventoryStockBundle\Form\Type\InventoryType;

class InventoryController extends AbstractController
{
    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;

    public function __construct(InventoryRepository $ir)
    {
        $this->inventoryRepository = $ir;
    }

    /**
     * Displaying paginated table od inventory records and a form
     * for adding/editing inventory records
     * 
     */

    public function index(Request $request) :Response
    {
        $page = $request->query->get('page', 1);

        return $this->render('@InventoryStock/index.html.twig', [
            'inventories' => $this->inventoryRepository->getAll($page),
            'form' => $this->createForm(InventoryType::class, null, [
                'action' => $this->generateUrl('_inventory_controller_store', ['page' => $page])
            ])->createView()
        ]);
    }

    /**
     * Storing a single inventory record into the database
     * 
     * After the inventory record was stored redirect to index
     */

    public function store(Request $request) :Response
    {
        $form = $this->createForm(InventoryType::class, new Inventory());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $inventory = $form->getData();

            $this->inventoryRepository->persist($inventory);
            $this->addFlash(
                'success',
                'Your changes were saved!'
            );
        } else {

            $this->addFlash(
                'error',
                'Form didn\'t pass validation'
            );
        }

        return $this->redirectToRoute('_inventory_controller_index', ['page' => $request->query->get('page', 1)]);
    }
}