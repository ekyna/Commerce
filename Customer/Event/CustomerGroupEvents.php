<?php

namespace Ekyna\Component\Commerce\Customer\Event;

/**
 * Class CustomerGroupEvents
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerGroupEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.customer_group.insert';
    const UPDATE      = 'ekyna_commerce.customer_group.update';
    const DELETE      = 'ekyna_commerce.customer_group.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.customer_group.initialize';

    const PRE_CREATE  = 'ekyna_commerce.customer_group.pre_create';
    const POST_CREATE = 'ekyna_commerce.customer_group.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.customer_group.pre_update';
    const POST_UPDATE = 'ekyna_commerce.customer_group.post_update';

    const PRE_DELETE  = 'ekyna_commerce.customer_group.pre_delete';
    const POST_DELETE = 'ekyna_commerce.customer_group.post_delete';
}
