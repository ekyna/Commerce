<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Message;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

use function array_intersect_key;
use function array_replace_recursive;

/**
 * Class OrderStateChangeMessage
 * @package Ekyna\Component\Commerce\Order\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * A message dispatched when an order state (general, payment, shipment and/or invoice) changes.
 *
 * @see \Ekyna\Component\Commerce\Order\EventListener\OrderListener::handleStateChange
 */
class OrderStateChange
{
    public static function create(OrderInterface $order, array $changeSet): self
    {
        $data = array_intersect_key($changeSet, self::DEFAULT);

        if (!isset($data[self::GENERAL])) {
            $data[self::GENERAL] = [
                $order->getState(),
                $order->getState(),
            ];
        }

        if (!isset($data[self::PAYMENT])) {
            $data[self::PAYMENT] = [
                $order->getPaymentState(),
                $order->getPaymentState(),
            ];
        }

        if (!isset($data[self::SHIPMENT])) {
            $data[self::SHIPMENT] = [
                $order->getShipmentState(),
                $order->getShipmentState(),
            ];
        }

        if (!isset($data[self::INVOICE])) {
            $data[self::INVOICE] = [
                $order->getInvoiceState(),
                $order->getInvoiceState(),
            ];
        }

        $data[self::SAMPLE] = $order->isSample();

        return new self($order->getId(), $data);
    }

    private const GENERAL  = 'state';
    private const PAYMENT  = 'paymentState';
    private const SHIPMENT = 'shipmentState';
    private const INVOICE  = 'invoiceState';
    private const SAMPLE   = 'sample';

    private const DEFAULT  = [
        self::GENERAL  => [OrderStates::STATE_NEW, OrderStates::STATE_NEW],
        self::PAYMENT  => [PaymentStates::STATE_NEW, PaymentStates::STATE_NEW],
        self::SHIPMENT => [ShipmentStates::STATE_NEW, ShipmentStates::STATE_NEW],
        self::INVOICE  => [InvoiceStates::STATE_NEW, InvoiceStates::STATE_NEW],
    ];

    private int   $orderId;
    private array $data;

    private function __construct(int $orderId, array $data)
    {
        $this->orderId = $orderId;
        $this->data = array_replace_recursive(self::DEFAULT, $data);
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getFromState(): string
    {
        return $this->data[self::GENERAL][0];
    }

    public function getToState(): string
    {
        return $this->data[self::GENERAL][1];
    }

    public function getFromPaymentState(): string
    {
        return $this->data[self::PAYMENT][0];
    }

    public function getToPaymentState(): string
    {
        return $this->data[self::PAYMENT][1];
    }

    public function getFromShipmentState(): string
    {
        return $this->data[self::SHIPMENT][0];
    }

    public function getToShipmentState(): string
    {
        return $this->data[self::SHIPMENT][1];
    }

    public function getFromInvoiceState(): string
    {
        return $this->data[self::INVOICE][0];
    }

    public function getToInvoiceState(): string
    {
        return $this->data[self::INVOICE][1];
    }

    public function isSample(): bool
    {
        return $this->data[self::SAMPLE];
    }
}
