<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Cart\Model\CartEventInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Resource\Event\PersistenceEvent;

/**
 * Class CartListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartListener
{
    /**
     * @var CalculatorInterface
     */
    protected $calculator;


    /**
     * Constructor.
     *
     * @param CalculatorInterface      $calculator
     */
    public function __construct(
        CalculatorInterface $calculator
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Insert event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onInsert(PersistenceEvent $event)
    {
        $cart = $this->getCartFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        $changed = false;

        // Handle identity
        $changed = $this->handleIdentity($cart) || $changed;

        // Handle addresses
        $changed = $this->handleAddresses($cart) || $changed;

        // Update totals
        $changed = $this->updateTotals($cart) || $changed;


        // TODO Timestampable behavior/listener
        $cart
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($cart);
        }
    }

    /**
     * Update event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $cart = $this->getCartFromEvent($event);

        // TODO same shit here ... T_T

        $changed = false;

        // Handle identity
        $changed = $this->handleIdentity($cart) || $changed;

        // Handle addresses
        if ($event->isChanged(['deliveryAddress', 'sameAddress'])) {
            $changed = $this->handleAddresses($cart) || $changed;
        }

        // TODO resolve/fix taxation adjustments if delivery address changed.
        // - Replace based on subject.
        // - If no subject, remove unexpected taxes ?

        // Update totals
        // TODO test that, maybe we have to use UnitOfWork::isCollectionScheduledFor*
        if ($event->isChanged(['items', 'adjustments', 'payments'])) {
            $changed = $this->updateTotals($cart) || $changed;
        }


        // TODO Timestampable behavior/listener
        $cart->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($cart);
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param CartEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(CartEventInterface $event)
    {
        /*$cart = $event->getCart();

        // Stop if order has valid payments
        if (null !== $payments = $cart->getPayments()) {
            $deletablePaymentStates = [PaymentStates::STATE_NEW, PaymentStates::STATE_CANCELLED];
            foreach ($payments as $payment) {
                if (!in_array($payment->getState(), $deletablePaymentStates)) {
                    throw new IllegalOperationException();
                }
            }
        }*/
    }

    /**
     * Handle the identity.
     *
     * @param CartInterface $cart
     *
     * @return bool Whether the cart has been changed or not.
     */
    protected function handleIdentity(CartInterface $cart)
    {
        $changed = false;

        if (null !== $customer = $cart->getCustomer()) {
            if (0 == strlen($cart->getEmail())) {
                $cart->setEmail($customer->getEmail());
                $changed = true;
            }
            if (0 == strlen($cart->getFirstName())) {
                $cart->setFirstName($customer->getFirstName());
                $changed = true;
            }
            if (0 == strlen($cart->getLastName())) {
                $cart->setLastName($customer->getLastName());
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * handle the addresses.
     *
     * @param CartInterface $cart
     *
     * @return bool Whether the cart has been changed or not.
     */
    protected function handleAddresses(CartInterface $cart)
    {
        if ((null !== $deliveryAddress = $cart->getDeliveryAddress()) && $cart->getSameAddress()) {
            // Unset delivery address
            $cart->setDeliveryAddress(null);

            // Delete the delivery address
            // TODO $this->manager->delete($deliveryAddress);

            return true;
        }

        return false;
    }

    /**
     * Updates the cart totals.
     *
     * @param CartInterface $cart
     *
     * @return bool Whether the cart has been changed or not.
     */
    protected function updateTotals(CartInterface $cart)
    {
        $amounts = $this->calculator->calculateSale($cart);

        $cart
            ->setNetTotal($amounts->getBase())
            ->setGrandTotal($amounts->getTotal());

        return false;
    }

    /**
     * Returns the cart from the event.
     *
     * @param PersistenceEvent $event
     *
     * @return CartInterface
     * @throws InvalidArgumentException
     */
    private function getCartFromEvent(PersistenceEvent $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CartInterface) {
            throw new InvalidArgumentException('Expected CartInterface');
        }

        return $resource;
    }
}
