<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Entity;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencySubjectInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class AbstractStockUnit
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnit extends AbstractResource implements Model\StockUnitInterface
{
    use StateSubjectTrait;

    protected ?Model\WarehouseInterface   $warehouse         = null;
    protected ?SupplierOrderItemInterface $supplierOrderItem = null;
    protected array                       $geocodes          = [];
    /** The estimated date of arrival (for ordered quantity). */
    protected ?DateTimeInterface $estimatedDateOfArrival = null;

    /** (default currency) */
    protected Decimal $netPrice;
    /** (default currency) */
    protected Decimal $shippingPrice;

    /** The quantity ordered to supplier. */
    protected Decimal $orderedQuantity;
    /** The quantity received by supplier. */
    protected Decimal $receivedQuantity;
    /** The quantity adjusted by administrators. */
    protected Decimal $adjustedQuantity;
    /** The quantity sold from sales. */
    protected Decimal $soldQuantity;
    /** The quantity shipped through sales. */
    protected Decimal $shippedQuantity;
    /** The quantity locked through preparations. */
    protected Decimal $lockedQuantity;

    protected DateTimeInterface  $createdAt;
    protected ?DateTimeInterface $closedAt = null;

    /** @var Collection<Model\StockAssignmentInterface> */
    protected Collection $stockAssignments;
    /** @var Collection<Model\StockAdjustmentInterface> */
    protected Collection $stockAdjustments;


    public function __construct()
    {
        $this->state = Model\StockUnitStates::STATE_NEW;
        $this->netPrice = new Decimal(0);
        $this->shippingPrice = new Decimal(0);
        $this->orderedQuantity = new Decimal(0);
        $this->receivedQuantity = new Decimal(0);
        $this->adjustedQuantity = new Decimal(0);
        $this->soldQuantity = new Decimal(0);
        $this->shippedQuantity = new Decimal(0);
        $this->lockedQuantity = new Decimal(0);
        $this->createdAt = new DateTime();
        $this->stockAssignments = new ArrayCollection();
        $this->stockAdjustments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        if (!empty($this->getGeocodes())) {
            return implode(' - ', $this->getGeocodes());
        } elseif (null !== $this->id) {
            return 'SU#' . $this->id;
        }

        return 'New stock unit';
    }

    public function getWarehouse(): ?Model\WarehouseInterface
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Model\WarehouseInterface $warehouse): Model\StockUnitInterface
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    public function getSupplierOrderItem(): ?SupplierOrderItemInterface
    {
        return $this->supplierOrderItem;
    }

    public function setSupplierOrderItem(?SupplierOrderItemInterface $item): Model\StockUnitInterface
    {
        if ($item === $this->supplierOrderItem) {
            return $this;
        }

        if ($previous = $this->supplierOrderItem) {
            $this->supplierOrderItem = null;
            $previous->setStockUnit(null);
        }

        if ($this->supplierOrderItem = $item) {
            $this->supplierOrderItem->setStockUnit($this);
        }

        return $this;
    }

    public function getGeocodes(): array
    {
        return $this->geocodes;
    }

    public function hasGeocode(string $geocode): bool
    {
        $geocode = strtoupper($geocode);

        return in_array($geocode, $this->geocodes, true);
    }

    public function addGeocode(string $geocode): Model\StockUnitInterface
    {
        $geocode = strtoupper($geocode);

        if (in_array($geocode, $this->geocodes, true)) {
            return $this;
        }

        $this->geocodes[] = $geocode;

        return $this;
    }

    public function removeGeocode(string $geocode): Model\StockUnitInterface
    {
        $geocode = strtoupper($geocode);

        if (false === $index = array_search($geocode, $this->geocodes, true)) {
            return $this;
        }

        unset($this->geocodes[$index]);

        return $this;
    }

    public function setGeocodes(array $codes): Model\StockUnitInterface
    {
        $this->geocodes = [];

        foreach ($codes as $code) {
            $this->addGeocode($code);
        }

        return $this;
    }

    public function getEstimatedDateOfArrival(): ?DateTimeInterface
    {
        return $this->estimatedDateOfArrival;
    }

    public function setEstimatedDateOfArrival(?DateTimeInterface $date): Model\StockUnitInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(Decimal $price): Model\StockUnitInterface
    {
        $this->netPrice = $price;

        return $this;
    }

    public function getShippingPrice(): Decimal
    {
        return $this->shippingPrice;
    }

    public function setShippingPrice(Decimal $price): Model\StockUnitInterface
    {
        $this->shippingPrice = $price;

        return $this;
    }

    public function getOrderedQuantity(): Decimal
    {
        return $this->orderedQuantity;
    }

    public function setOrderedQuantity(Decimal $quantity): Model\StockUnitInterface
    {
        $this->orderedQuantity = $quantity;

        return $this;
    }

    public function getReceivedQuantity(): Decimal
    {
        return $this->receivedQuantity;
    }

    public function setReceivedQuantity(Decimal $quantity): Model\StockUnitInterface
    {
        $this->receivedQuantity = $quantity;

        return $this;
    }

    public function getAdjustedQuantity(): Decimal
    {
        return $this->adjustedQuantity;
    }

    public function setAdjustedQuantity(Decimal $quantity): Model\StockUnitInterface
    {
        $this->adjustedQuantity = $quantity;

        return $this;
    }

    public function getSoldQuantity(): Decimal
    {
        return $this->soldQuantity;
    }

    public function setSoldQuantity(Decimal $quantity): Model\StockUnitInterface
    {
        $this->soldQuantity = $quantity;

        return $this;
    }

    public function getShippedQuantity(): Decimal
    {
        return $this->shippedQuantity;
    }

    public function setShippedQuantity(Decimal $quantity): Model\StockUnitInterface
    {
        $this->shippedQuantity = $quantity;

        return $this;
    }

    public function getLockedQuantity(): Decimal
    {
        return $this->lockedQuantity;
    }

    public function setLockedQuantity(Decimal $quantity): Model\StockUnitInterface
    {
        $this->lockedQuantity = $quantity;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $date): Model\StockUnitInterface
    {
        $this->createdAt = $date;

        return $this;
    }

    public function getClosedAt(): ?DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?DateTimeInterface $date): Model\StockUnitInterface
    {
        $this->closedAt = $date;

        return $this;
    }

    public function hasStockAssignment(Model\StockAssignmentInterface $assignment): bool
    {
        return $this->stockAssignments->contains($assignment);
    }

    public function addStockAssignment(Model\StockAssignmentInterface $assignment): Model\StockUnitInterface
    {
        if ($this->hasStockAssignment($assignment)) {
            return $this;
        }

        $this->stockAssignments->add($assignment);
        $assignment->setStockUnit($this);

        return $this;
    }

    public function removeStockAssignment(Model\StockAssignmentInterface $assignment): Model\StockUnitInterface
    {
        if (!$this->hasStockAssignment($assignment)) {
            return $this;
        }

        $this->stockAssignments->removeElement($assignment);
        $assignment->setStockUnit(null);

        return $this;
    }

    public function getStockAssignments(): Collection
    {
        return $this->stockAssignments;
    }

    public function hasStockAdjustment(Model\StockAdjustmentInterface $adjustment): bool
    {
        return $this->stockAdjustments->contains($adjustment);
    }

    public function addStockAdjustment(Model\StockAdjustmentInterface $adjustment): Model\StockUnitInterface
    {
        if ($this->hasStockAdjustment($adjustment)) {
            return $this;
        }

        $this->stockAdjustments->add($adjustment);
        $adjustment->setStockUnit($this);

        return $this;
    }

    public function removeStockAdjustment(Model\StockAdjustmentInterface $adjustment): Model\StockUnitInterface
    {
        if (!$this->hasStockAdjustment($adjustment)) {
            return $this;
        }

        $this->stockAdjustments->removeElement($adjustment);
        $adjustment->setStockUnit(null);

        return $this;
    }

    public function getStockAdjustments(): Collection
    {
        return $this->stockAdjustments;
    }

    public function isEmpty(): bool
    {
        return null === $this->supplierOrderItem
            && $this->stockAssignments->isEmpty()
            && $this->stockAdjustments->isEmpty()
            && $this->orderedQuantity->isZero()
            && $this->adjustedQuantity->isZero()
            && $this->soldQuantity->isZero();
    }

    public function isClosed(): bool
    {
        return $this->state === Model\StockUnitStates::STATE_CLOSED;
    }

    public function getReservableQuantity(): Decimal
    {
        if (0 == $this->orderedQuantity + $this->adjustedQuantity) {
            return new Decimal(INF);
        }

        return max($this->orderedQuantity + $this->adjustedQuantity - $this->soldQuantity, new Decimal(0));
    }

    public function getReleasableQuantity(): Decimal
    {
        return max($this->soldQuantity - $this->shippedQuantity - $this->lockedQuantity, new Decimal(0));
    }

    public function getShippableQuantity(): Decimal
    {
        return max(
            $this->receivedQuantity + $this->adjustedQuantity - $this->shippedQuantity - $this->lockedQuantity,
            new Decimal(0)
        );
    }

    public function getSupplierOrder(): ?SupplierOrderInterface
    {
        return $this->supplierOrderItem?->getOrder();
    }

    public function getCurrency(): ?CurrencyInterface
    {
        return $this->supplierOrderItem?->getOrder()->getCurrency();
    }

    public function getExchangeRate(): ?Decimal
    {
        return $this->supplierOrderItem?->getOrder()->getExchangeRate();
    }

    public function getExchangeDate(): ?DateTimeInterface
    {
        return $this->supplierOrderItem?->getOrder()->getExchangeDate();
    }

    public function getBaseCurrency(): ?string
    {
        // Stock unit price are in default currency
        return null;
    }

    public function setCurrency(?CurrencyInterface $currency): CurrencySubjectInterface
    {
        throw new LogicException('Set currency on associated supplier order.');
    }

    public function setExchangeRate(?Decimal $rate): ExchangeSubjectInterface
    {
        throw new LogicException('Set exchange rate on associated supplier order.');
    }

    public function setExchangeDate(?DateTimeInterface $date): ExchangeSubjectInterface
    {
        throw new LogicException('Set exchange rate on associated supplier order.');
    }
}
