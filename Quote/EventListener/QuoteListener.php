<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
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

        $quote = $this->getSaleFromEvent($event);

        // Set the default 'expires at' date time
        $date = new \DateTime();
        $date->modify($this->expirationDelay)->setTime(0, 0, 0, 0);
        $quote->setExpiresAt($date);
    }

    /**
     * @inheritDoc
     *
     * @param QuoteInterface $sale
     */
    protected function handleInsert(SaleInterface $sale)
    {
        $changed = parent::handleInsert($sale);

        $changed |= $this->handleEditable($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param QuoteInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale)
    {
        $changed = parent::handleUpdate($sale);

        $changed |= $this->handleEditable($sale);

        return $changed;
    }

    /**
     * @param QuoteInterface $quote
     *
     * @return bool
     */
    protected function handleEditable(QuoteInterface $quote)
    {
        if (!$quote->isEditable()) {
            return false;
        }

        $changed = false;

        if (!$quote->isAutoDiscount()) {
            $quote->setAutoDiscount(true);
            $changed = true;
        }

        if (!$quote->isAutoShipping()) {
            $quote->setAutoShipping(true);
            $changed = true;
        }

        if (!$quote->isAutoNotify()) {
            $quote->setAutoNotify(true);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    protected function updateState(SaleInterface $sale)
    {
        if (parent::updateState($sale)) {
            /** @var QuoteInterface $sale */
            if (($sale->getState() === QuoteStates::STATE_ACCEPTED) && (null === $sale->getAcceptedAt())) {
                $sale->setAcceptedAt(new \DateTime());
            } elseif (($sale->getState() !== QuoteStates::STATE_ACCEPTED) && (null !== $sale->getAcceptedAt())) {
                $sale->setAcceptedAt(null);
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     * @return QuoteInterface
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
