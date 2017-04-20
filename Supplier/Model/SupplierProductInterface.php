<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface SupplierProductInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductInterface extends SubjectRelativeInterface, TimestampableInterface
{
    public function getSupplier(): ?SupplierInterface;

    public function setSupplier(?SupplierInterface $supplier): SupplierProductInterface;

    public function getAvailableStock(): Decimal;

    public function setAvailableStock(Decimal $stock): SupplierProductInterface;

    public function getOrderedStock(): Decimal;

    public function setOrderedStock(Decimal $stock): SupplierProductInterface;

    public function getEstimatedDateOfArrival(): ?DateTimeInterface;

    public function setEstimatedDateOfArrival(?DateTimeInterface $date): SupplierProductInterface;
}
