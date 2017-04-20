<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Event;

/**
 * Class PaymentEvents
 * @package Ekyna\Component\Commerce\Payment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentEvents
{
    public const AUTHORIZE = 'ekyna_commerce.payment.authorize';
    public const CAPTURE   = 'ekyna_commerce.payment.capture';
    public const PAYOUT    = 'ekyna_commerce.payment.payout';
    public const CANCEL    = 'ekyna_commerce.payment.cancel';
    public const HANG      = 'ekyna_commerce.payment.hang';
    public const ACCEPT    = 'ekyna_commerce.payment.accept';
    public const REFUND    = 'ekyna_commerce.payment.refund';
    public const REJECT    = 'ekyna_commerce.payment.reject';
    public const STATUS    = 'ekyna_commerce.payment.status';
}
