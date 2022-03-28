<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class InvoiceAvailability
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceAvailability
{
    private ?SaleItemInterface $item;
    private Decimal            $expected;
    private Decimal            $maximum;
    /** @var array<InvoiceAvailability> */
    private array $children;

    public function __construct(?SaleItemInterface $item, Decimal $expected, Decimal $maximum)
    {
        $this->item = $item;
        $this->expected = $expected;
        $this->maximum = $maximum;
        $this->children = [];
    }

    public function setExpected(Decimal $expected, bool $spread): InvoiceAvailability
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

    public function setMaximum(Decimal $maximum, bool $spread): InvoiceAvailability
    {
        $this->maximum = $maximum;

        if (!$spread) {
            return $this;
        }

        foreach ($this->children as $child) {
            $e = $maximum->mul($child->getItem()->getQuantity());

            if ($child->getMaximum() > $e) {
                $child->setMaximum($e, true);
            }
        }

        return $this;
    }

    public function addChild(InvoiceAvailability $availability): void
    {
        $this->children[] = $availability;
    }

    public function getItem(): ?SaleItemInterface
    {
        return $this->item;
    }

    public function getExpected(): Decimal
    {
        return $this->expected;
    }

    public function getMaximum(): Decimal
    {
        return $this->maximum;
    }
}
