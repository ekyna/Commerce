<?php

namespace Ekyna\Component\Commerce\Newsletter\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Newsletter\Event\SubscriptionEvents;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\IsEnabledTrait;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class MemberEventListener
 * @package Ekyna\Component\Commerce\Newsletter\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberListener implements ListenerInterface
{
    use IsEnabledTrait;

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param CustomerRepositoryInterface      $customerRepository
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        CustomerRepositoryInterface $customerRepository,
        ResourceEventDispatcherInterface $dispatcher
    ) {
        $this->persistenceHelper  = $persistenceHelper;
        $this->customerRepository = $customerRepository;
        $this->dispatcher         = $dispatcher;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event): void
    {
        //$member = $this->getMemberFromEvent($event);
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event): void
    {
        $member = $this->getMemberFromEvent($event);

        $this->setCustomer($member);
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $member = $this->getMemberFromEvent($event);

        $this->setCustomer($member);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $member = $this->getMemberFromEvent($event);

        if (!$this->persistenceHelper->isChanged($member, 'email')) {
            return;
        }

        $this->setCustomer($member->setCustomer(null));

        if (!$this->enabled) {
            return;
        }

        // Emails has changed -> Schedule subscriptions change events
        foreach ($member->getSubscriptions() as $subscription) {
            $this->persistenceHelper->scheduleEvent(SubscriptionEvents::UPDATE, $subscription);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onSubscriptionChange(ResourceEventInterface $event): void
    {
        $member = $this->getMemberFromEvent($event);

        $status = SubscriptionStatus::UNSUBSCRIBED;
        foreach ($member->getSubscriptions() as $subscription) {
            if (SubscriptionStatus::SUBSCRIBED !== $subscription->getStatus()) {
                continue;
            }

            $status = SubscriptionStatus::SUBSCRIBED;
            break;
        }

        if ($status === $member->getStatus()) {
            return;
        }

        $member->setStatus($status);

        $this->persistenceHelper->persistAndRecompute($member, false);
    }

    /**
     * Links the member to its customer.
     *
     * @param MemberInterface $member
     */
    protected function setCustomer(MemberInterface $member): void
    {
        if ($member->getCustomer()) {
            $this->persistenceHelper->persistAndRecompute($member, false);

            return;
        }

        if (!$customer = $this->customerRepository->findOneByEmail($member->getEmail())) {
            return;
        }

        $member->setCustomer($customer);

        $this->persistenceHelper->persistAndRecompute($member, false);

        if ($member->getStatus() !== SubscriptionStatus::SUBSCRIBED) {
            return;
        }

        $this->persistenceHelper->scheduleEvent(CustomerEvents::NEWSLETTER_SUBSCRIBE, $customer);
    }

    /**
     * Returns the member from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return MemberInterface
     */
    protected function getMemberFromEvent(ResourceEventInterface $event): MemberInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof MemberInterface) {
            throw new InvalidArgumentException('Expected instance of ' . MemberInterface::class);
        }

        return $resource;
    }
}
