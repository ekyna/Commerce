<?php

namespace Ekyna\Component\Commerce\Payment\Event;

/**
 * Class PaymentMethodEvents
 * @package Ekyna\Component\Commerce\Payment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentMethodEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.payment_method.insert';
    const UPDATE = 'ekyna_commerce.payment_method.update';
    const DELETE = 'ekyna_commerce.payment_method.delete';
}
