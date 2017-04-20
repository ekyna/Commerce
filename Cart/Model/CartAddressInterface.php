<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAddressInterface;

/**
 * Interface CartAddressInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartAddressInterface extends SaleAddressInterface
{
    /**
     * Returns the cart this address is the invoice one.
     */
    public function getInvoiceCart(): ?CartInterface;

    /**
     * Sets the cart this address is the invoice one.
     */
    public function setInvoiceCart(?CartInterface $cart): CartAddressInterface;

    /**
     * Returns the cart this address is the delivery one.
     */
    public function getDeliveryCart(): ?CartInterface;

    /**
     * Sets the cart this address is the delivery one.
     */
    public function setDeliveryCart(?CartInterface $cart): CartAddressInterface;

    public function getCart(): ?CartInterface;
}
