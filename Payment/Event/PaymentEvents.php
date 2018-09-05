<?php

namespace Ekyna\Component\Commerce\Payment\Event;

/**
 * Class PaymentEvents
 * @package Ekyna\Component\Commerce\Payment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentEvents
{
    const CAPTURE = 'ekyna_commerce.payment.capture';
    const CANCEL  = 'ekyna_commerce.payment.cancel';
    const HANG    = 'ekyna_commerce.payment.hang';
    const ACCEPT  = 'ekyna_commerce.payment.accept';
    const REFUND  = 'ekyna_commerce.payment.refund';
    const STATUS  = 'ekyna_commerce.payment.status';
}
