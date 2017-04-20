<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class NotificationTypes
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class NotificationTypes
{
    public const MANUAL             = 'manual';
    public const CART_REMIND        = 'cart_remind';
    public const ORDER_ACCEPTED     = 'order_accepted';
    public const QUOTE_REMIND       = 'quote_remind';
    public const PAYMENT_AUTHORIZED = 'payment_authorized';
    public const PAYMENT_CAPTURED   = 'payment_captured';
    public const PAYMENT_PAYEDOUT   = 'payment_payedout';
    public const PAYMENT_EXPIRED    = 'payment_expired';
    public const SHIPMENT_READY     = 'shipment_ready';
    public const SHIPMENT_COMPLETE  = 'shipment_complete';
    public const SHIPMENT_PARTIAL   = 'shipment_partial';
    public const INVOICE_COMPLETE   = 'invoice_complete';
    public const INVOICE_PARTIAL    = 'invoice_partial';
    public const RETURN_PENDING     = 'return_pending';
    public const RETURN_RECEIVED    = 'return_received';


    public static function getTypes(): array
    {
        return [
            NotificationTypes::MANUAL,
            NotificationTypes::CART_REMIND,
            NotificationTypes::ORDER_ACCEPTED,
            NotificationTypes::QUOTE_REMIND,
            NotificationTypes::PAYMENT_AUTHORIZED,
            NotificationTypes::PAYMENT_CAPTURED,
            NotificationTypes::PAYMENT_PAYEDOUT,
            NotificationTypes::PAYMENT_EXPIRED,
            NotificationTypes::SHIPMENT_READY,
            NotificationTypes::SHIPMENT_COMPLETE,
            NotificationTypes::SHIPMENT_PARTIAL,
            NotificationTypes::INVOICE_COMPLETE,
            NotificationTypes::INVOICE_PARTIAL,
            NotificationTypes::RETURN_PENDING,
            NotificationTypes::RETURN_RECEIVED,
        ];
    }

    public static function isValid(string $type, bool $throw = true): bool
    {
        if (in_array($type, NotificationTypes::getTypes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid notification type.');
        }

        return false;
    }

    private function __construct()
    {
    }
}
