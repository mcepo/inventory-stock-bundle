<?php
namespace SF9\InventoryStockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SF9\InventoryStockBundle\Repository\InventoryRepository")
 * @ORM\Table(name="inventory",uniqueConstraints={@UniqueConstraint(name="unique_product_location", columns={"sku", "branch"})})
 *
 * Defines the properties of the Inventory entity.
 */
class Inventory
{

   /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=15)
     */
    private $sku;

    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=5)
     */
    private $branch;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    private $stock;

    public function __construct($data = [])
    {
        if(is_array($data)) {
            $this->sku = $data['sku'] ?? null;
            $this->branch = $data['branch'] ?? null;
            $this->stock = $data['stock'] ?? null;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function setBranch(string $branch): void
    {
        $this->branch = $branch;
    }

    public function getStock(): ?float
    {
        return $this->stock;
    }

    public function setStock(string $stock): void
    {
        $this->stock = $stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->sku, $this->branch, $this->stock];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->sku, $this->branch, $this->stock] = $data;
    }
}
