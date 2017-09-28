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

    // Domain
    const INITIALIZE  = 'ekyna_commerce.payment_method.initialize';

    const PRE_CREATE  = 'ekyna_commerce.payment_method.pre_create';
    const POST_CREATE = 'ekyna_commerce.payment_method.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.payment_method.pre_update';
    const POST_UPDATE = 'ekyna_commerce.payment_method.post_update';

    const PRE_DELETE  = 'ekyna_commerce.payment_method.pre_delete';
    const POST_DELETE = 'ekyna_commerce.payment_method.post_delete';
}
