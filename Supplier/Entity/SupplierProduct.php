<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class SupplierProduct
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProduct implements SupplierProductInterface
{
    use SubjectRelativeTrait;
    use TimestampableTrait;

    protected ?SupplierInterface $supplier               = null;
    protected Decimal            $packing;
    protected Decimal            $availableStock;
    protected Decimal            $orderedStock;
    protected ?DateTimeInterface $estimatedDateOfArrival = null;


    public function __construct()
    {
        $this->initializeSubjectRelative();

        $this->packing = new Decimal(1);
        $this->availableStock = new Decimal(0);
        $this->orderedStock = new Decimal(0);
    }

    public function __toString(): string
    {
        if (empty($this->designation)) {
            return 'New supplier product';
        }

        if (1 !== $this->packing->toInt()) {
            return $this->designation . ' (x' . Units::round($this->packing, $this->unit) . ')';
        }

        return $this->designation;
    }

    public function getSupplier(): ?SupplierInterface
    {
        return $this->supplier;
    }

    public function setSupplier(?SupplierInterface $supplier): SupplierProductInterface
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getPacking(): Decimal
    {
        return $this->packing;
    }

    public function setPacking(Decimal $packing): SupplierProductInterface
    {
        $this->packing = $packing;

        return $this;
    }

    public function getAvailableStock(): Decimal
    {
        return $this->availableStock;
    }

    public function setAvailableStock(Decimal $stock): SupplierProductInterface
    {
        $this->availableStock = $stock;

        return $this;
    }

    public function getOrderedStock(): Decimal
    {
        return $this->orderedStock;
    }

    public function setOrderedStock(Decimal $stock): SupplierProductInterface
    {
        $this->orderedStock = $stock;

        return $this;
    }

    public function getEstimatedDateOfArrival(): ?DateTimeInterface
    {
        return $this->estimatedDateOfArrival;
    }

    public function setEstimatedDateOfArrival(?DateTimeInterface $date): SupplierProductInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }
}
