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
    const DONE    = 'ekyna_commerce.payment.done';
    const NOTIFY  = 'ekyna_commerce.payment.notify';
    const CANCEL  = 'ekyna_commerce.payment.cancel';
}
