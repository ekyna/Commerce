<?php

namespace Ekyna\Component\Commerce\Customer\Event;

/**
 * Class CustomerAddressEvents
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerAddressEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.customer_address.insert';
    const UPDATE      = 'ekyna_commerce.customer_address.update';
    const DELETE      = 'ekyna_commerce.customer_address.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.customer_address.initialize';

    const PRE_CREATE  = 'ekyna_commerce.customer_address.pre_create';
    const POST_CREATE = 'ekyna_commerce.customer_address.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.customer_address.pre_update';
    const POST_UPDATE = 'ekyna_commerce.customer_address.post_update';

    const PRE_DELETE  = 'ekyna_commerce.customer_address.pre_delete';
    const POST_DELETE = 'ekyna_commerce.customer_address.post_delete';
}
