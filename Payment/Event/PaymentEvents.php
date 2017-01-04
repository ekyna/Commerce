<?php

namespace Ekyna\Component\Commerce\Payment\Event;

/**
 * Class PaymentEvents
 * @package Ekyna\Component\Commerce\Payment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentEvents
{
    const PREPARE = 'ekyna_commerce.payment.done';
    const DONE    = 'ekyna_commerce.payment.done';
    const NOTIFY  = 'ekyna_commerce.payment.done';
}
