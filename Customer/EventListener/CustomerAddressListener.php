<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class CustomerAddressListener
 * @package Ekyna\Component\Commerce\Customer\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressListener
{
    protected PersistenceHelperInterface $persistenceHelper;
    protected bool                       $enabled = true;

    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Sets whether this listener is enabled.
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Pre delete event handler.
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $address = $this->getAddressFromEvent($event);

        if (null === $customer = $address->getCustomer()) {
            return;
        }

        if ($address->isInvoiceDefault()) {
            if (!($customer->hasParent() && null !== $customer->getParent()->getDefaultInvoiceAddress())) {
                throw new IllegalOperationException(); // TODO reason message
            }
        } elseif ($address->isDeliveryDefault()) {
            if (!($customer->hasParent() && null !== $customer->getParent()->getDefaultDeliveryAddress())) {
                throw new IllegalOperationException(); // TODO reason message
            }
        }
    }

    /**
     * Insert event handler.
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $address = $this->getAddressFromEvent($event);

        $this->fixInvoiceDefault($address);
        $this->fixDeliveryDefault($address);
    }

    /**
     * Update event handler.
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $address = $this->getAddressFromEvent($event);

        $this->fixInvoiceDefault($address);
        $this->fixDeliveryDefault($address);
    }

    /**
     * Fixes the default invoice address.
     */
    protected function fixInvoiceDefault(CustomerAddressInterface $address): void
    {
        if (!$this->persistenceHelper->isChanged($address, ['invoiceDefault'])) {
            return;
        }

        $customer = $address->getCustomer();

        if ($address->isInvoiceDefault()) {
            foreach ($customer->getAddresses() as $a) {
                if ($a === $address) {
                    continue;
                }

                if ($a->isInvoiceDefault()) {
                    $a->setInvoiceDefault(false);
                    $this->persistenceHelper->persistAndRecompute($a, false);
                }
            }
        } elseif (null === $customer->getDefaultInvoiceAddress(true)) {
            $address->setInvoiceDefault(true);
            $this->persistenceHelper->persistAndRecompute($address, false);
        }
    }

    /**
     * Fix the default delivery address.
     */
    protected function fixDeliveryDefault(CustomerAddressInterface $address): void
    {
        if (!$this->persistenceHelper->isChanged($address, ['deliveryDefault'])) {
            return;
        }

        $customer = $address->getCustomer();

        if ($address->isDeliveryDefault()) {
            foreach ($customer->getAddresses() as $a) {
                if ($a === $address) {
                    continue;
                }

                if ($a->isDeliveryDefault()) {
                    $a->setDeliveryDefault(false);
                    $this->persistenceHelper->persistAndRecompute($a, false);
                }
            }
        } elseif (null === $customer->getDefaultDeliveryAddress(true)) {
            $address->setDeliveryDefault(true);
            $this->persistenceHelper->persistAndRecompute($address, false);
        }
    }

    /**
     * Returns the address from the event.
     */
    protected function getAddressFromEvent(ResourceEventInterface $event): CustomerAddressInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerAddressInterface) {
            throw new UnexpectedTypeException($resource, CustomerAddressInterface::class);
        }

        return $resource;
    }
}
