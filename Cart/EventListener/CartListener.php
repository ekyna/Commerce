<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartListener extends AbstractSaleListener
{
    /**
     * @var ShipmentPriceResolverInterface
     */
    protected $shipmentPriceResolver;

    /**
     * @var string
     */
    protected $expirationDelay;


    /**
     * Sets the shipment price resolver.
     *
     * @param ShipmentPriceResolverInterface $resolver
     */
    public function setShipmentPriceResolver(ShipmentPriceResolverInterface $resolver)
    {
        $this->shipmentPriceResolver = $resolver;
    }

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
    protected function handleInsert(SaleInterface $sale)
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
    protected function handleUpdate(SaleInterface $sale)
    {
        $changed = parent::handleUpdate($sale);

        if ($this->persistenceHelper->isChanged($sale, ['shipmentMethod', 'customerGroup'])) {
            $changed |= $this->updateShipmentMethodAndAmount($sale);
        }

        $changed |= $this->updateExpiresAt($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     */
    protected function handleAddressChange(SaleInterface $sale)
    {
        $changed = parent::handleAddressChange($sale);

        if ($this->didDeliveryCountryChanged($sale)) {
            $changed |= $this->updateShipmentMethodAndAmount($sale);
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    protected function handleContentChange(SaleInterface $sale)
    {
        $changed = $this->updateShipmentMethodAndAmount($sale);

        $changed |= parent::handleContentChange($sale);

        return $changed;
    }

    /**
     * Updates the cart's shipment method and amount if needed.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function updateShipmentMethodAndAmount(SaleInterface $sale)
    {
        $updated = false;
        $prices = $this->shipmentPriceResolver->getAvailablePricesBySale($sale);

        // Assert that the sale's shipment method is still available
        if (null !== $method = $sale->getShipmentMethod()) {
            $found = false;
            foreach ($prices as $price) {
                if ($price->getMethod() === $sale->getShipmentMethod()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $sale->setShipmentMethod(null);
                $updated = true;
            }
        }

        // If sale does not have a shipment method, set the cheaper one
        if (null === $sale->getShipmentMethod()) {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface $price */
            if (false !== $price = reset($prices)) {
                $sale->setShipmentMethod($price->getMethod());
                $updated = true;
            }
        }

        // Resolve shipping cost
        $amount = 0;
        if (null !== $price = $this->shipmentPriceResolver->getPriceBySale($sale)) {
            $amount = $price->isFree() ? 0 : $price->getNetPrice();
        }

        // Update sale's shipping cost if needed
        if ($amount != $sale->getShipmentAmount()) {
            $sale->setShipmentAmount($amount);
            $updated = true;
        }

        return $updated;
    }

    /**
     * Updates the cart expiration date.
     *
     * @param CartInterface $cart
     *
     * @return bool
     */
    protected function updateExpiresAt(CartInterface $cart)
    {
        $date = new \DateTime();
        $date->modify($this->expirationDelay);
        $cart->setExpiresAt($date);

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateState(SaleInterface $sale)
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
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
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
    protected function scheduleContentChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        $this->persistenceHelper->scheduleEvent(CartEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleStateChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        $this->persistenceHelper->scheduleEvent(CartEvents::STATE_CHANGE, $sale);
    }
}
