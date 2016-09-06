<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Order\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderEventInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Event\PersistenceEvent;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListener
{
    /**
     * @var NumberGeneratorInterface
     */
    protected $generator;

    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $generator
     * @param CalculatorInterface      $calculator
     * @param StateResolverInterface   $stateResolver
     */
    public function __construct(
        NumberGeneratorInterface $generator,
        CalculatorInterface $calculator,
        StateResolverInterface $stateResolver
    ) {
        $this->generator = $generator;
        $this->calculator = $calculator;
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onInsert(PersistenceEvent $event)
    {
        $order = $this->getOrderFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        $changed = false;

        // Generate number and key
        $changed = $this->generateNumber($order) || $changed;
        $changed = $this->generateKey($order) || $changed;

        // Handle identity
        $changed = $this->handleIdentity($order) || $changed;

        // Handle addresses
        $changed = $this->handleAddresses($order) || $changed;

        // Update totals
        $changed = $this->updateTotals($order) || $changed;


        // TODO Timestampable behavior/listener
        $order
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($order);
        }
    }

    /**
     * Update event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $order = $this->getOrderFromEvent($event);

        // TODO same shit here ... T_T

        $changed = false;

        // Generate number and key
        //$changed = $this->generateNumber($order) || $changed;
        //$changed = $this->generateKey($order) || $changed;

        // Handle identity
        $changed = $this->handleIdentity($order) || $changed;

        // Handle addresses
        if ($event->isChanged(['deliveryAddress', 'sameAddress'])) {
            $changed = $this->handleAddresses($order) || $changed;
        }

        // TODO resolve/fix taxation adjustments if delivery address changed.
        // - Replace based on subject.
        // - If no subject, remove unexpected taxes ?

        // Update totals
        // TODO test that, maybe we have to use UnitOfWork::isCollectionScheduledFor*
        if ($event->isChanged(['items', 'adjustments', 'payments'])) {
            $changed = $this->updateTotals($order) || $changed;
        }


        // TODO Timestampable behavior/listener
        $order->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($order);
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param OrderEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(OrderEventInterface $event)
    {
        $order = $event->getOrder();

        // Stop if order has valid payments
        if (null !== $payments = $order->getPayments()) {
            $deletablePaymentStates = [PaymentStates::STATE_NEW, PaymentStates::STATE_CANCELLED];
            foreach ($payments as $payment) {
                if (!in_array($payment->getState(), $deletablePaymentStates)) {
                    throw new IllegalOperationException();
                }
            }
        }

        // Stop if order has valid shipments
        if (null !== $shipments = $order->getShipments()) {
            $deletableShipmentStates = [ShipmentStates::STATE_CHECKOUT, ShipmentStates::STATE_CANCELLED];
            foreach ($shipments as $shipment) {
                if (!in_array($shipment->getState(), $deletableShipmentStates)) {
                    throw new IllegalOperationException();
                }
            }
        }
    }

    /**
     * Generates the order number.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the order number has been generated or not.
     */
    protected function generateNumber(OrderInterface $order)
    {
        if (0 == strlen($order->getNumber())) {
            $this->generator->generateNumber($order);

            return true;
        }

        return false;
    }

    /**
     * Generates the order key.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the order key has been generated or not.
     */
    protected function generateKey(OrderInterface $order)
    {
        if (0 == strlen($order->getKey())) {
            $this->generator->generateKey($order);

            return true;
        }

        return false;
    }

    /**
     * Handle the identity.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the order has been changed or not.
     */
    protected function handleIdentity(OrderInterface $order)
    {
        $changed = false;

        if (null !== $customer = $order->getCustomer()) {
            if (0 == strlen($order->getEmail())) {
                $order->setEmail($customer->getEmail());
                $changed = true;
            }
            /* TODO if (0 == strlen($order->getGender())) {
                $order->setGender($customer->getGender());
            }*/
            if (0 == strlen($order->getFirstName())) {
                $order->setFirstName($customer->getFirstName());
                $changed = true;
            }
            if (0 == strlen($order->getLastName())) {
                $order->setLastName($customer->getLastName());
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * handle the addresses.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the order has been changed or not.
     */
    protected function handleAddresses(OrderInterface $order)
    {
        if ((null !== $deliveryAddress = $order->getDeliveryAddress()) && $order->getSameAddress()) {
            // Unset delivery address
            $order->setDeliveryAddress(null);

            // Delete the delivery address
            // TODO $this->manager->delete($deliveryAddress);

            return true;
        }

        return false;
    }

    /**
     * Updates the order totals.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the order has been changed or not.
     */
    protected function updateTotals(OrderInterface $order)
    {
        $amounts = $this->calculator->calculateSale($order);

        $order
            ->setNetTotal($amounts->getBase())
            ->setGrandTotal($amounts->getTotal());

        return false;
    }

    /**
     * Fills the address identity fields if needed.
     *
     * @param AddressInterface $address
     * @param UserInterface    $user
     */
    /*private function handleAddressIdentity(AddressInterface $address, UserInterface $user)
    {
        if (null === $address->getGender()) {
            $address->setGender($user->getGender());
        }
        if (null === $address->getFirstName()) {
            $address->setFirstName($user->getFirstName());
        }
        if (null === $address->getLastName()) {
            $address->setLastName($user->getLastName());
        }
    }*/

    /**
     * Returns the order from the event.
     *
     * @param PersistenceEvent $event
     *
     * @return OrderInterface
     * @throws InvalidArgumentException
     */
    private function getOrderFromEvent(PersistenceEvent $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInterface) {
            throw new InvalidArgumentException('Expected OrderInterface');
        }

        return $resource;
    }
}
