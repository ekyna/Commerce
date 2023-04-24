<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Class SupplierOrderItemInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderItemInterface extends SubjectRelativeInterface
{
    public function getOrder(): ?SupplierOrderInterface;

    public function setOrder(?SupplierOrderInterface $order): SupplierOrderItemInterface;

    public function getProduct(): ?SupplierProductInterface;

    public function setProduct(?SupplierProductInterface $product): SupplierOrderItemInterface;

    public function getStockUnit(): ?StockUnitInterface;

    public function setStockUnit(?StockUnitInterface $stockUnit): SupplierOrderItemInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): SupplierOrderItemInterface;

    public function getPacking(): Decimal;

    public function setPacking(Decimal $packing): SupplierOrderItemInterface;

    public function getDeliveryItems(): Collection;

    public function getSubjectQuantity(): Decimal;
}
