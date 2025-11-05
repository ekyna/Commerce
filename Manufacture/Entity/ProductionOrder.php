<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceTrait;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class ProductionOrder
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrder extends AbstractResource implements ProductionOrderInterface
{
    use SubjectReferenceTrait;
    use NumberSubjectTrait;
    use TimestampableTrait;

    protected ?BillOfMaterialsInterface $bom = null;
    protected ?WarehouseInterface       $warehouse = null;
    protected ?StockUnitInterface       $stockUnit = null;
    protected POState                   $state;
    protected ?DateTimeInterface        $startAt = null;
    protected ?DateTimeInterface        $endAt   = null;
    protected ?int                      $quantity = null;
    /** @var Collection<ProductionItem> */
    protected Collection                $items;
    /** @var Collection<ProductionInterface> */
    protected Collection                $productions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = POState::NEW;

        $this->items = new ArrayCollection();
        $this->productions = new ArrayCollection();

        $this->initializeSubjectIdentity();
    }

    public function __toString(): string
    {
        return (string)$this->number;
    }

    public function getBom(): ?BillOfMaterialsInterface
    {
        return $this->bom;
    }

    public function setBom(?BillOfMaterialsInterface $bom): ProductionOrderInterface
    {
        $this->bom = $bom;

        return $this;
    }

    public function getWarehouse(): ?WarehouseInterface
    {
        return $this->warehouse;
    }

    public function setWarehouse(?WarehouseInterface $warehouse): ProductionOrderInterface
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    public function getStockUnit(): ?StockUnitInterface
    {
        return $this->stockUnit;
    }

    public function setStockUnit(?StockUnitInterface $stockUnit): ProductionOrderInterface
    {
        $this->stockUnit = $stockUnit;

        return $this;
    }

    public function getState(): POState
    {
        return $this->state;
    }

    public function setState(POState $state): ProductionOrderInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeInterface $startAt): ProductionOrderInterface
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeInterface $endAt): ProductionOrderInterface
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): ProductionOrderInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): ProductionOrderInterface
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return Collection<ProductionInterface>
     */
    public function getProductions(): Collection
    {
        return $this->productions;
    }

    public function hasProduction(ProductionInterface $production): bool
    {
        return $this->productions->contains($production);
    }

    public function addProduction(ProductionInterface $production): ProductionOrderInterface
    {
        if (!$this->hasProduction($production)) {
            $this->productions->add($production);
            $production->setProductionOrder($this);
        }

        return $this;
    }

    public function removeProduction(ProductionInterface $production): ProductionOrderInterface
    {
        if ($this->hasProduction($production)) {
            $this->productions->removeElement($production);
            $production->setProductionOrder(null);
        }

        return $this;
    }

    /**
     * @param Collection<ProductionInterface> $productions
     */
    public function setProductions(Collection $productions): ProductionOrderInterface
    {
        $this->productions = $productions;

        return $this;
    }
}
