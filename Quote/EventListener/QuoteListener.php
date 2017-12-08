<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteListener extends AbstractSaleListener
{
    /**
     * @var string
     */
    protected $expirationDelay;


    /**
     * Sets the expiration delay.
     *
     * @param string $delay
     */
    public function setExpirationDelay($delay)
    {
        $this->expirationDelay = $delay;
    }

    /**
     * @inheritdoc
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        parent::onInitialize($event);

        /** @var \Ekyna\Bundle\CommerceBundle\Entity\Quote $quote */
        $quote = $this->getSaleFromEvent($event);

        // Set the default 'expires at' date time
        $date = new \DateTime();
        $date->modify($this->expirationDelay)->setTime(0, 0, 0);
        $quote->setExpiresAt($date);
    }

    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface");
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function scheduleContentChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface");
        }

        $this->persistenceHelper->scheduleEvent(QuoteEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleStateChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface");
        }

        $this->persistenceHelper->scheduleEvent(QuoteEvents::STATE_CHANGE, $sale);
    }
}
