<?php

namespace Ekyna\Component\Commerce\Newsletter\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
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

        $this->getGateway($member->getAudience()->getGateway(), GatewayInterface::CREATE_MEMBER);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $member = $this->getMemberFromEvent($event);

        $this->getGateway($member->getAudience()->getGateway(), GatewayInterface::UPDATE_MEMBER);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        $member = $this->getMemberFromEvent($event);

        $this->getGateway($member->getAudience()->getGateway(), GatewayInterface::DELETE_MEMBER);
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $member = $this->getMemberFromEvent($event);

        $gateway = $this->getGateway($member->getAudience()->getGateway(), GatewayInterface::CREATE_MEMBER);

        if ($gateway->createMember($member)) {
            $this->persistenceHelper->persistAndRecompute($member, false);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $member = $this->getMemberFromEvent($event);

        $gateway = $this->getGateway($member->getAudience()->getGateway(), GatewayInterface::UPDATE_MEMBER);

        if ($gateway->updateMember($member, $this->persistenceHelper->getChangeSet($member))) {
            $this->persistenceHelper->persistAndRecompute($member, false);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $member = $this->getMemberFromEvent($event);

        $this
            ->getGateway($member->getAudience()->getGateway(), GatewayInterface::DELETE_MEMBER)
            ->deleteMember($member);
    }

    /**
     * Returns the gateway.
     *
     * @param string $name   The gateway name
     * @param string $action The gateway action
     *
     * @return GatewayInterface
     *
     * @throws NewsletterException If the action is not supported by this gateway
     */
    protected function getGateway(string $name, string $action)
    {
        $gateway = $this->gatewayRegistry->get($name);

        if (!$gateway->supports($action)) {
            throw new NewsletterException(
                "Can't $action with gateway '$name'. Please use their website."
            );
        }

        return $gateway;
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
