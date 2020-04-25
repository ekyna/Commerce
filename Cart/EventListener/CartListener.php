<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartListener extends AbstractSaleListener
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
     * @inheritDoc
     *
     * @param CartInterface $sale
     */
    protected function handleInsert(SaleInterface $sale): bool
    {
        $changed = parent::handleInsert($sale);

        $changed |= $this->updateExpiresAt($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param CartInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale): bool
    {
        $changed = parent::handleUpdate($sale);

        $changed |= $this->updateExpiresAt($sale);

        return $changed;
    }

    /**
     * Updates the cart expiration date.
     *
     * @param CartInterface $cart
     *
     * @return bool
     */
    protected function updateExpiresAt(CartInterface $cart): bool
    {
        $date = new \DateTime();
        $date->modify($this->expirationDelay);
        $cart->setExpiresAt($date);

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateState(SaleInterface $sale): bool
    {
        if (parent::updateState($sale)) {
            /** @var CartInterface $sale */
            if (($sale->getState() === CartStates::STATE_ACCEPTED) && (null === $sale->getAcceptedAt())) {
                $sale->setAcceptedAt(new \DateTime());
            } elseif (($sale->getState() !== CartStates::STATE_ACCEPTED) && (null !== $sale->getAcceptedAt())) {
                $sale->setAcceptedAt(null);
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     * @return CartInterface
     */
    protected function getSaleFromEvent(ResourceEventInterface $event): SaleInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function scheduleContentChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        $this->persistenceHelper->scheduleEvent(CartEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleStateChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        $this->persistenceHelper->scheduleEvent(CartEvents::STATE_CHANGE, $sale);
    }
}
