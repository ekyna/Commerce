<?php

namespace Ekyna\Component\Commerce\Newsletter\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\IsEnabledTrait;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class SubscriptionListener
 * @package Ekyna\Component\Commerce\Newsletter\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionListener implements ListenerInterface
{
    use IsEnabledTrait;

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var GatewayRegistry
     */
    private $gatewayRegistry;

    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param GatewayRegistry            $gatewayRegistry
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper, GatewayRegistry $gatewayRegistry)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->gatewayRegistry   = $gatewayRegistry;
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event): void
    {
        $subscription = $this->getSubscriptionFromEvent($event);

        $gateway = $this->getGateway($subscription);
        $gateway->createSubscription($subscription);
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $subscription = $this->getSubscriptionFromEvent($event);

        $member = $subscription->getMember();

        $this->scheduleMemberSubscriptionChangeEvent($member);

        if (!$this->enabled) {
            return;
        }

        $gateway = $this->getGateway($subscription);
        $gateway->insertSubscription($subscription);

        $this->persistenceHelper->persistAndRecompute($member, false);
        $this->persistenceHelper->persistAndRecompute($subscription, false);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $subscription = $this->getSubscriptionFromEvent($event);

        if ($this->persistenceHelper->isChanged($subscription, 'status')) {
            $this->scheduleMemberSubscriptionChangeEvent($subscription->getMember());
        }

        if (!$this->enabled) {
            return;
        }

        $gateway = $this->getGateway($subscription);

        $gateway->updateSubscription(
            $subscription,
            $this->persistenceHelper->getChangeSet($subscription),
            $this->persistenceHelper->getChangeSet($subscription->getMember())
        );

        $this->persistenceHelper->persistAndRecompute($subscription, false);
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $subscription = $this->getSubscriptionFromEvent($event);

        if (null === $member = $subscription->getMember()) {
            $member = $this->persistenceHelper->getChangeSet($subscription, 'member')[0];
        }

        $this->scheduleMemberSubscriptionChangeEvent($member);

        if (!$this->enabled) {
            return;
        }

        $gateway = $this->getGateway($subscription);

        $gateway->deleteSubscription($subscription);
    }

    /**
     * Returns the gateway.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return GatewayInterface
     */
    protected function getGateway(SubscriptionInterface $subscription): GatewayInterface
    {
        if (null === $audience = $subscription->getAudience()) {
            $audience = $this->persistenceHelper->getChangeSet($subscription, 'audience')[0];
        }

        return $this->gatewayRegistry->get($audience->getGateway());
    }

    /**
     * Returns the subscription from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SubscriptionInterface
     */
    protected function getSubscriptionFromEvent(ResourceEventInterface $event): SubscriptionInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof SubscriptionInterface) {
            throw new InvalidArgumentException('Expected instance of ' . SubscriptionInterface::class);
        }

        return $resource;
    }

    /**
     * Schedules the member 'subscription change' event.
     *
     * @param MemberInterface $member
     */
    protected function scheduleMemberSubscriptionChangeEvent(MemberInterface $member): void
    {
        $this->persistenceHelper->scheduleEvent($member, MemberEvents::SUBSCRIPTION_CHANGE);
    }
}
