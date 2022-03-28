<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class ShipmentAvailability
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAvailability
{
    private SaleItemInterface $item;
    private Decimal           $expected;
    private Decimal           $assigned;
    private ?Decimal          $inStock;
    /** @var array<ShipmentAvailability> */
    private array $children;

    public function __construct(SaleItemInterface $item, Decimal $expected, Decimal $available, Decimal $inStock = null)
    {
        $this->item = $item;
        $this->expected = $expected;
        $this->assigned = $available;
        $this->inStock = $inStock;
        $this->children = [];
    }

    public function setExpected(Decimal $expected, bool $spread): ShipmentAvailability
    {
        $this->expected = $expected;

        if (!$spread) {
            return $this;
        }

        foreach ($this->children as $child) {
            $e = $expected->mul($child->getItem()->getQuantity());

            if ($child->getExpected() > $e) {
                $child->setExpected($e, true);
            }
        }

        return $this;
    }

    public function setAssigned(Decimal $assigned, bool $spread): ShipmentAvailability
    {
        $this->assigned = $assigned;

        if (!$spread) {
            return $this;
        }

        foreach ($this->children as $child) {
            $a = $assigned->mul($child->getItem()->getQuantity());

            if ($child->getAssigned() > $a) {
                $child->setAssigned($a, true);
            }
        }

        return $this;
    }

    public function setInStock(?Decimal $inStock): ShipmentAvailability
    {
        $this->inStock = $inStock;

        return $this;
    }

    public function addChild(ShipmentAvailability $availability): void
    {
        $this->children[] = $availability;
    }

    public function getItem(): SaleItemInterface
    {
        return $this->item;
    }

    /**
     * The expected quantity.
     */
    public function getExpected(): Decimal
    {
        return $this->expected;
    }

    /**
     * The available assigned quantity.
     */
    public function getAssigned(): Decimal
    {
        return $this->assigned;
    }

    /**
     * The available in stock quantity.
     */
    public function getInStock(): ?Decimal
    {
        return $this->inStock;
    }
}
