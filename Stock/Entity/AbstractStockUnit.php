<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Class AbstractStockUnit
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnit implements Model\StockUnitInterface
{
    use StateSubjectTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\WarehouseInterface
     */
    protected $warehouse;

    /**
     * @var SupplierOrderItemInterface
     */
    protected $supplierOrderItem;

    /**
     * @var array
     */
    protected $geocodes;

    /**
     * The estimated date of arrival (for ordered quantity).
     *
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;

    /**
     *  (default currency)
     * @var float
     */
    protected $netPrice;

    /**
     *  (default currency)
     * @var float
     */
    protected $shippingPrice;

    /**
     * The quantity ordered to supplier.
     *
     * @var float
     */
    protected $orderedQuantity;

    /**
     * The quantity received by supplier.
     *
     * @var float
     */
    protected $receivedQuantity;

    /**
     * The quantity adjusted by administrators.
     *
     * @var float
     */
    protected $adjustedQuantity;

    /**
     * The quantity sold from sales.
     *
     * @var float
     */
    protected $soldQuantity;

    /**
     * The quantity shipped through sales.
     *
     * @var float
     */
    protected $shippedQuantity;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $closedAt;

    /**
     * @var ArrayCollection|Model\StockAssignmentInterface[]
     */
    protected $stockAssignments;

    /**
     * @var ArrayCollection|Model\StockAdjustmentInterface[]
     */
    protected $stockAdjustments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\StockUnitStates::STATE_NEW;
        $this->geocodes = [];
        $this->netPrice = 0.;
        $this->shippingPrice = 0.;
        $this->orderedQuantity = 0.;
        $this->receivedQuantity = 0.;
        $this->adjustedQuantity = 0.;
        $this->soldQuantity = 0.;
        $this->shippedQuantity = 0.;
        $this->createdAt = new \DateTime();
        $this->stockAssignments = new ArrayCollection();
        $this->stockAdjustments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if (!empty($this->getGeocodes())) {
            return implode('-', $this->getGeocodes());
        } elseif (null !== $this->getId()) {
            return '#' . $this->getId();
        }

        return 'Unknown';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the warehouse.
     *
     * @return Model\WarehouseInterface
     */
    public function getWarehouse(): ?Model\WarehouseInterface
    {
        return $this->warehouse;
    }

    /**
     * Sets the warehouse.
     *
     * @param Model\WarehouseInterface $warehouse
     *
     * @return $this|Model\StockUnitInterface
     */
    public function setWarehouse(Model\WarehouseInterface $warehouse): Model\StockUnitInterface
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSupplierOrderItem(): ?SupplierOrderItemInterface
    {
        return $this->supplierOrderItem;
    }

    /**
     * @inheritDoc
     */
    public function setSupplierOrderItem(SupplierOrderItemInterface $item = null): Model\StockUnitInterface
    {
        if ($item !== $this->supplierOrderItem) {
            if ($previous = $this->supplierOrderItem) {
                $this->supplierOrderItem = null;
                $previous->setStockUnit(null);
            }

            if ($this->supplierOrderItem = $item) {
                $this->supplierOrderItem->setStockUnit($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGeocodes(): array
    {
        return $this->geocodes;
    }

    /**
     * @inheritDoc
     */
    public function hasGeocode(string $geocode): bool
    {
        $geocode = strtoupper($geocode);

        return in_array($geocode, $this->geocodes, true);
    }

    /**
     * @inheritDoc
     */
    public function addGeocode(string $geocode): Model\StockUnitInterface
    {
        $geocode = strtoupper($geocode);

        if (!in_array($geocode, $this->geocodes, true)) {
            $this->geocodes[] = $geocode;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeGeocode(string $geocode): Model\StockUnitInterface
    {
        $geocode = strtoupper($geocode);

        if (false !== $index = array_search($geocode, $this->geocodes, true)) {
            unset($this->geocodes[$index]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setGeocodes(array $codes): Model\StockUnitInterface
    {
        $this->geocodes = $codes;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEstimatedDateOfArrival(): ?\DateTime
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * @inheritDoc
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null): Model\StockUnitInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNetPrice(): float
    {
        return $this->netPrice;
    }

    /**
     * @inheritDoc
     */
    public function setNetPrice(float $price): Model\StockUnitInterface
    {
        $this->netPrice = $price;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getShippingPrice(): float
    {
        return $this->shippingPrice;
    }

    /**
     * @inheritDoc
     */
    public function setShippingPrice(float $price): Model\StockUnitInterface
    {
        $this->shippingPrice = $price;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrderedQuantity(): float
    {
        return $this->orderedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setOrderedQuantity(float $quantity): Model\StockUnitInterface
    {
        $this->orderedQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getReceivedQuantity(): float
    {
        return $this->receivedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setReceivedQuantity(float $quantity): Model\StockUnitInterface
    {
        $this->receivedQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAdjustedQuantity(): float
    {
        return $this->adjustedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setAdjustedQuantity(float $quantity): Model\StockUnitInterface
    {
        $this->adjustedQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSoldQuantity(): float
    {
        return $this->soldQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setSoldQuantity(float $quantity): Model\StockUnitInterface
    {
        $this->soldQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getShippedQuantity(): float
    {
        return $this->shippedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setShippedQuantity(float $quantity): Model\StockUnitInterface
    {
        $this->shippedQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(\DateTime $date): Model\StockUnitInterface
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClosedAt(): ?\DateTime
    {
        return $this->closedAt;
    }

    /**
     * @inheritDoc
     */
    public function setClosedAt(\DateTime $date = null): Model\StockUnitInterface
    {
        $this->closedAt = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasStockAssignment(Model\StockAssignmentInterface $assignment): bool
    {
        return $this->stockAssignments->contains($assignment);
    }

    /**
     * @inheritDoc
     */
    public function addStockAssignment(Model\StockAssignmentInterface $assignment): Model\StockUnitInterface
    {
        if (!$this->hasStockAssignment($assignment)) {
            $this->stockAssignments->add($assignment);
            $assignment->setStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeStockAssignment(Model\StockAssignmentInterface $assignment): Model\StockUnitInterface
    {
        if ($this->hasStockAssignment($assignment)) {
            $this->stockAssignments->removeElement($assignment);
            $assignment->setStockUnit(null);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStockAssignments(): Collection
    {
        return $this->stockAssignments;
    }

    /**
     * @inheritDoc
     */
    public function hasStockAdjustment(Model\StockAdjustmentInterface $adjustment): bool
    {
        return $this->stockAdjustments->contains($adjustment);
    }

    /**
     * @inheritDoc
     */
    public function addStockAdjustment(Model\StockAdjustmentInterface $adjustment): Model\StockUnitInterface
    {
        if (!$this->hasStockAdjustment($adjustment)) {
            $this->stockAdjustments->add($adjustment);
            $adjustment->setStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeStockAdjustment(Model\StockAdjustmentInterface $adjustment): Model\StockUnitInterface
    {
        if ($this->hasStockAdjustment($adjustment)) {
            $this->stockAdjustments->removeElement($adjustment);
            $adjustment->setStockUnit(null);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStockAdjustments(): Collection
    {
        return $this->stockAdjustments;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return null === $this->supplierOrderItem
            && 0 == $this->stockAssignments->count()
            && 0 == $this->orderedQuantity
            && 0 == $this->soldQuantity
            && 0 == $this->adjustedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->state === Model\StockUnitStates::STATE_CLOSED;
    }

    /**
     * @inheritDoc
     */
    public function getReservableQuantity(): float
    {
        if (0 == $this->orderedQuantity + $this->adjustedQuantity) {
            return INF;
        }

        $result = $this->orderedQuantity + $this->adjustedQuantity - $this->soldQuantity;

        return max($result, 0);
    }

    /**
     * @inheritDoc
     */
    public function getShippableQuantity(): float
    {
        $result = $this->receivedQuantity + $this->adjustedQuantity - $this->shippedQuantity;

        return max($result, 0);
    }

    /**
     * @inheritDoc
     */
    public function getSupplierOrder(): ?SupplierOrderInterface
    {
        if ($this->supplierOrderItem) {
            return $this->supplierOrderItem->getOrder();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): ?CurrencyInterface
    {
        if ($this->supplierOrderItem) {
            return $this->supplierOrderItem->getOrder()->getCurrency();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(): ?float
    {
        if ($this->supplierOrderItem) {
            return $this->supplierOrderItem->getOrder()->getExchangeRate();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getExchangeDate(): ?\DateTime
    {
        if ($this->supplierOrderItem) {
            return $this->supplierOrderItem->getOrder()->getExchangeDate();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getBaseCurrency(): ?string
    {
        // Stock unit price are in default currency
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        throw new LogicException("Set currency on associated supplier order.");
    }

    /**
     * @inheritDoc
     */
    public function setExchangeRate(float $rate = null): ExchangeSubjectInterface
    {
        throw new LogicException("Set exchange rate on associated supplier order.");
    }

    /**
     * @inheritDoc
     */
    public function setExchangeDate(\DateTime $date = null): ExchangeSubjectInterface
    {
        throw new LogicException("Set exchange rate on associated supplier order.");
    }
}
