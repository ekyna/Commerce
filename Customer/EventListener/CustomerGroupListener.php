<?php

namespace Ekyna\Component\Commerce\Customer\EventListener;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class CustomerGroupListener
 * @package Ekyna\Component\Commerce\Customer\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        CustomerGroupRepositoryInterface $customerGroupRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->customerGroupRepository = $customerGroupRepository;
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
        $group = $this->getCustomerGroupFromEvent($event);

        if ($group->isDefault()) {
            throw new IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $group = $this->getCustomerGroupFromEvent($event);

        $this->fixDefault($group);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $address = $this->getCustomerGroupFromEvent($event);

        $this->fixDefault($address);
    }

    /**
     * Fixes the default customer group.
     *
     * @param CustomerGroupInterface $customerGroup
     */
    protected function fixDefault(CustomerGroupInterface $customerGroup)
    {
        if (!$this->persistenceHelper->isChanged($customerGroup, ['default'])) {
            return;
        }

        if ($customerGroup->isDefault()) {
            $previousGroup = $this->customerGroupRepository->findDefault();
            if ($previousGroup === $customerGroup) {
                return;
            }

            $previousGroup->setDefault(false);

            $this->persistenceHelper->persistAndRecompute($previousGroup, false);
        }
    }

    /**
     * Returns the customer group from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CustomerGroupInterface
     * @throws InvalidArgumentException
     */
    protected function getCustomerGroupFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerGroupInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerGroupInterface::class);
        }

        return $resource;
    }
}
