<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CustomerAddressInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerAddressInterface extends ResourceInterface, AddressInterface
{
    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): CustomerAddressInterface;

    /**
     * Returns whether this is the default invoice address.
     */
    public function isInvoiceDefault(): bool;

    /**
     * Sets whether this is the default invoice address.
     */
    public function setInvoiceDefault(bool $default): CustomerAddressInterface;

    /**
     * Returns whether this is the default delivery address.
     */
    public function isDeliveryDefault(): bool;

    /**
     * Sets whether this is the default delivery address.
     */
    public function setDeliveryDefault(bool $default): CustomerAddressInterface;
}
