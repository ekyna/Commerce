<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\EventListener\CustomerListener;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerEventSubscriber extends CustomerListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        parent::onUpdate($event);

        $customer = $this->getCustomerFromEvent($event);

        if ($this->persistenceHelper->isChanged($customer, ['inCharge'])) {
            $this->scheduleParentChangeEvents($customer);
        }
    }

    /**
     * @inheritDoc
     */
    protected function updateFromParent(CustomerInterface $customer)
    {
        $changed = parent::updateFromParent($customer);

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if ($customer->hasParent() && null === $customer->getInCharge()) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $parent */
            $parent = $customer->getParent();
            if (null !== $inCharge = $parent->getInCharge()) {
                $customer->setInCharge($inCharge);

                $changed = true;
            }
        }

        return $changed;
    }


    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerEvents::INSERT        => ['onInsert', 0],
            CustomerEvents::UPDATE        => ['onUpdate', 0],
            CustomerEvents::PARENT_CHANGE => ['onParentChange', 0],
        ];
    }
}
