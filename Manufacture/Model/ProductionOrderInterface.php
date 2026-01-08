<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Class ProductionOrder
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionOrderInterface
    extends SubjectReferenceInterface,
            ResourceInterface,
            NumberSubjectInterface,
            TimestampableInterface
{
    public function getBom(): ?BillOfMaterialsInterface;

    public function setBom(?BillOfMaterialsInterface $bom): ProductionOrderInterface;

    public function getWarehouse(): ?WarehouseInterface;

    public function setWarehouse(?WarehouseInterface $warehouse): ProductionOrderInterface;

    public function getStockUnit(): ?StockUnitInterface;

    public function setStockUnit(?StockUnitInterface $stockUnit): ProductionOrderInterface;

    public function getState(): POState;

    public function setState(POState $state): ProductionOrderInterface;

    public function getStartAt(): ?DateTimeInterface;

    public function setStartAt(?DateTimeInterface $startAt): ProductionOrderInterface;

    public function getEndAt(): ?DateTimeInterface;

    public function setEndAt(?DateTimeInterface $endAt): ProductionOrderInterface;

    public function getQuantity(): ?int;

    public function setQuantity(?int $quantity): ProductionOrderInterface;

    /**
     * @return Collection<ProductionItemInterface>
     */
    public function getItems(): Collection;

    /**
     * @param Collection<ProductionItemInterface> $items
     */
    public function setItems(Collection $items): ProductionOrderInterface;

    /**
     * @return Collection<ProductionInterface>
     */
    public function getProductions(): Collection;

    public function hasProduction(ProductionInterface $production): bool;

    public function addProduction(ProductionInterface $production): ProductionOrderInterface;

    public function removeProduction(ProductionInterface $production): ProductionOrderInterface;

    /**
     * @param Collection<ProductionInterface> $productions
     */
    public function setProductions(Collection $productions): ProductionOrderInterface;
}
