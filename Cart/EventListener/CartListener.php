<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartListener extends AbstractSaleListener
{
    protected string $expirationDelay;

    public function setExpirationDelay(string $delay): void
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

        return $this->updateExpiresAt($sale) || $changed;
    }

    /**
     * @inheritDoc
     *
     * @param CartInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale): bool
    {
        $changed = parent::handleUpdate($sale);

        return $this->updateExpiresAt($sale) || $changed;
    }

    /**
     * Updates the cart expiration date.
     */
    protected function updateExpiresAt(CartInterface $cart): bool
    {
        $date = new \DateTime();
        $date->modify($this->expirationDelay);
        $cart->setExpiresAt($date);

        return true;
    }

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
     * @inheritDoc
     *
     * @return CartInterface
     */
    protected function getSaleFromEvent(ResourceEventInterface $event): SaleInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof CartInterface) {
            throw new UnexpectedTypeException($resource, CartInterface::class);
        }

        return $resource;
    }

    protected function scheduleContentChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof CartInterface) {
            throw new UnexpectedTypeException($sale, CartInterface::class);
        }

        $this->persistenceHelper->scheduleEvent($sale, CartEvents::CONTENT_CHANGE);
    }

    protected function scheduleStateChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof CartInterface) {
            throw new UnexpectedTypeException($sale, CartInterface::class);
        }

        $this->persistenceHelper->scheduleEvent($sale, CartEvents::STATE_CHANGE);
    }
}
