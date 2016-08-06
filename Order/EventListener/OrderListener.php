<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Order\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Order\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderEventInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

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
     * Pre create event handler.
     *
     * @param OrderEventInterface $event
     */
    public function onPreCreate(OrderEventInterface $event)
    {
        $order = $event->getOrder();

        $order
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        // Generate number and key
        $this->generateNumberAndKey($order);

        // Handle identity
        $this->handleIdentity($order);

        // Handle addresses
        $this->handleAddresses($order);

        // Update totals
        $this->updateTotals($order);
    }

    /**
     * Pre update event handler.
     *
     * @param OrderEventInterface $event
     */
    public function onPreUpdate(OrderEventInterface $event)
    {
        $order = $event->getOrder();

        $order->setUpdatedAt(new \DateTime());

        // Generate number and key
        $this->generateNumberAndKey($order);

        // Handle identity
        $this->handleIdentity($order);

        // Handle addresses
        $this->handleAddresses($order);

        // Update totals
        $this->updateTotals($order);
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
     * Generates the order number and key.
     *
     * @param OrderInterface $order
     */
    protected function generateNumberAndKey(OrderInterface $order)
    {
        $this->generator
            ->generateNumber($order)
            ->generateKey($order);
    }

    /**
     * handle the identity.
     *
     * @param OrderInterface $order
     */
    protected function handleIdentity(OrderInterface $order)
    {
        if (null !== $customer = $order->getCustomer()) {
            if (0 == strlen($order->getEmail())) {
                $order->setEmail($customer->getEmail());
            }
            /* TODO if (0 == strlen($order->getGender())) {
                $order->setGender($customer->getGender());
            }*/
            if (0 == strlen($order->getFirstName())) {
                $order->setFirstName($customer->getFirstName());
            }
            if (0 == strlen($order->getLastName())) {
                $order->setLastName($customer->getLastName());
            }
        }
    }

    /**
     * handle the addresses.
     *
     * @param OrderInterface $order
     */
    protected function handleAddresses(OrderInterface $order)
    {
        if ((null !== $deliveryAddress = $order->getDeliveryAddress()) && $order->getSameAddress()) {
            // Unset delivery address
            $order->setDeliveryAddress(null);
            // Delete the delivery address
            // TODO $this->manager->delete($deliveryAddress);
        }
    }

    /**
     * Updates the order totals.
     *
     * @param OrderInterface $order
     */
    protected function updateTotals(OrderInterface $order)
    {
        // TODO
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
}
