<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends Common\SaleInterface, ShipmentSubjectInterface, InvoiceSubjectInterface
{
    /**
     * Sets whether the order contains sample items.
     */
    public function setSample(bool $sample): OrderInterface;

    /**
     * Sets whether the sample order is released.
     */
    public function setReleased(bool $released): OrderInterface;

    /**
     * Returns whether the order is the customer's first.
     */
    public function isFirst(): bool;

    /**
     * Sets whether the order is the customer's first.
     */
    public function setFirst(bool $first): OrderInterface;

    public function getOriginCustomer(): ?CustomerInterface;

    public function setOriginCustomer(?CustomerInterface $customer): OrderInterface;

    public function getCompletedAt(): ?DateTimeInterface;

    public function setCompletedAt(?DateTimeInterface $completedAt): OrderInterface;

    public function getRevenueTotal(): ?Decimal;

    public function setRevenueTotal(?Decimal $amount): OrderInterface;

    public function getMarginTotal(): ?Decimal;

    public function setMarginTotal(?Decimal $amount): OrderInterface;

    public function getItemsCount(): int;

    public function setItemsCount(int $count): OrderInterface;
}
