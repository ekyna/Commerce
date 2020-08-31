<?php

namespace Ekyna\Component\Commerce\Newsletter\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Updater\AudienceUpdater;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\IsEnabledTrait;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AudienceEventListener
 * @package Ekyna\Component\Commerce\Newsletter\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceListener implements ListenerInterface
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
     * @var AudienceUpdater
     */
    private $audienceUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param GatewayRegistry            $gatewayRegistry
     * @param AudienceUpdater            $audienceUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        GatewayRegistry $gatewayRegistry,
        AudienceUpdater $audienceUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->gatewayRegistry   = $gatewayRegistry;
        $this->audienceUpdater   = $audienceUpdater;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event): void
    {
        $audience = $this->getAudienceFromEvent($event);

        $this->audienceUpdater->generateKey($audience);
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event): void
    {
        $audience = $this->getAudienceFromEvent($event);

        $this->getGateway($audience->getGateway(), GatewayInterface::INSERT_AUDIENCE);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $audience = $this->getAudienceFromEvent($event);

        $this->getGateway($audience->getGateway(), GatewayInterface::UPDATE_AUDIENCE);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        $audience = $this->getAudienceFromEvent($event);

        if ($audience->isDefault()) {
            throw new IllegalOperationException("Can't remove default audience.");
        }

        $this->getGateway($audience->getGateway(), GatewayInterface::DELETE_AUDIENCE);
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $audience = $this->getAudienceFromEvent($event);

        $this->audienceUpdater->fixDefault($audience);

        if (!$this->enabled) {
            return;
        }

        $this
            ->getGateway($audience->getGateway(), GatewayInterface::INSERT_AUDIENCE)
            ->insertAudience($audience);

        $this->persistenceHelper->persistAndRecompute($audience);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $audience = $this->getAudienceFromEvent($event);

        $changeSet = $this->persistenceHelper->getChangeSet($audience);

        if (isset($changeSet['gateway'])) {
            throw new IllegalOperationException("Changing audience gateway is not supported.");
        }

        $this->audienceUpdater->fixDefault($audience);

        if (!$this->enabled) {
            return;
        }

        $this
            ->getGateway($audience->getGateway(), GatewayInterface::UPDATE_AUDIENCE)
            ->updateAudience($audience, $changeSet);

        $this->persistenceHelper->persistAndRecompute($audience);
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

        $audience = $this->getAudienceFromEvent($event);

        $this
            ->getGateway($audience->getGateway(), GatewayInterface::DELETE_AUDIENCE)
            ->deleteAudience($audience);
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
     * Returns the audience from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return AudienceInterface
     */
    protected function getAudienceFromEvent(ResourceEventInterface $event): AudienceInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof AudienceInterface) {
            throw new InvalidArgumentException('Expected instance of ' . AudienceInterface::class);
        }

        return $resource;
    }
}
