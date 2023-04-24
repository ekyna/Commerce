<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierDeliveryItem
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierDeliveryItemInterface extends ResourceInterface
{
    public function getDelivery(): ?SupplierDeliveryInterface;

    public function setDelivery(?SupplierDeliveryInterface $delivery): SupplierDeliveryItemInterface;

    public function getOrderItem(): ?SupplierOrderItemInterface;

    public function setOrderItem(?SupplierOrderItemInterface $item): SupplierDeliveryItemInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): SupplierDeliveryItemInterface;

    public function getGeocode(): ?string;

    public function setGeocode(?string $geocode): SupplierDeliveryItemInterface;

    public function getSubjectQuantity(): Decimal;
}
