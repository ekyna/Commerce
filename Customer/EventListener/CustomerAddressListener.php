<?php

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class CustomerAddressListener
 * @package Ekyna\Component\Commerce\Customer\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface         $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
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
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $address = $this->getAddressFromEvent($event);

        $this->fixInvoiceDefault($address);
        $this->fixDeliveryDefault($address);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $address = $this->getAddressFromEvent($event);

        $this->fixInvoiceDefault($address);
        $this->fixDeliveryDefault($address);
    }

    /**
     * Fixes the default invoice address.
     *
     * @param CustomerAddressInterface $address
     */
    protected function fixInvoiceDefault(CustomerAddressInterface $address)
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
     *
     * @param CustomerAddressInterface $address
     */
    protected function fixDeliveryDefault(CustomerAddressInterface $address)
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
     *
     * @param ResourceEventInterface $event
     *
     * @return CustomerAddressInterface
     * @throws InvalidArgumentException
     */
    protected function getAddressFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerAddressInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerAddressInterface::class);
        }

        return $resource;
    }
}
