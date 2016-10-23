<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractSaleAddressListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleAddressListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Sets the resource event dispatcher.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $address = $this->getAddressFromEvent($event);

        // Dispatch
        if ($this->persistenceHelper->isChanged($address, 'country')) {
            $this->dispatchSaleTaxResolutionEvent($address);
        }
    }

    /**
     * Dispatches the sale tax resolution event.
     *
     * @param Model\AddressInterface $address
     */
    abstract protected function dispatchSaleTaxResolutionEvent(Model\AddressInterface $address);

    /**
     * Returns the sale item from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\AddressInterface
     */
    abstract protected function getAddressFromEvent(ResourceEventInterface $event);
}
