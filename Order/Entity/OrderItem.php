<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Class OrderItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItem extends AbstractSaleItem implements Model\OrderItemInterface
{
    protected ?Model\OrderInterface $order = null;

    /** @var Collection|StockAssignmentInterface[] */
    protected Collection $stockAssignments;


    public function __construct()
    {
        parent::__construct();

        $this->stockAssignments = new ArrayCollection();
    }

    public function getSale(): ?Common\SaleInterface
    {
        return $this->getOrder();
    }

    /**
     * @param Model\OrderInterface|null $sale
     */
    public function setSale(?Common\SaleInterface $sale): Common\SaleItemInterface
    {
        if (null !== $sale) {
            $this->assertSaleClass($sale);
        }

        $this->setOrder($sale);

        return $this;
    }

    public function getOrder(): ?Model\OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?Model\OrderInterface $order): Model\OrderItemInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeItem($this);
        }

        if ($this->order = $order) {
            $this->order->addItem($this);
        }

        return $this;
    }

    public function hasStockAssignment(StockAssignmentInterface $assignment): bool
    {
        return $this->stockAssignments->contains($assignment);
    }

    public function addStockAssignment(StockAssignmentInterface $assignment): StockAssignmentsInterface
    {
        if ($this->hasStockAssignment($assignment)) {
            return $this;
        }

        $this->stockAssignments->add($assignment);
        $assignment->setSaleItem($this);

        return $this;
    }

    public function removeStockAssignment(StockAssignmentInterface $assignment): StockAssignmentsInterface
    {
        if (!$this->hasStockAssignment($assignment)) {
            return $this;
        }

        $this->stockAssignments->removeElement($assignment);
        $assignment->setSaleItem(null);

        return $this;
    }

    public function hasStockAssignments(): bool
    {
        return 0 < $this->stockAssignments->count();
    }

    public function getStockAssignments(): Collection
    {
        return $this->stockAssignments;
    }

    protected function assertSaleClass(Common\SaleInterface $sale): void
    {
        if (!$sale instanceof Model\OrderInterface) {
            throw new UnexpectedTypeException($sale, Model\OrderInterface::class);
        }
    }

    protected function assertItemClass(Common\SaleItemInterface $child): void
    {
        if (!$child instanceof Model\OrderItemInterface) {
            throw new UnexpectedTypeException($child, Model\OrderItemInterface::class);
        }
    }

    protected function assertItemAdjustmentClass(Common\AdjustmentInterface $adjustment): void
    {
        if (!$adjustment instanceof Model\OrderItemAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\OrderItemAdjustmentInterface::class);
        }
    }
}
