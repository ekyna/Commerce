<?php

namespace Ekyna\Component\Commerce\Customer\Event;

/**
 * Class CustomerEvents
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.customer.insert';
    const UPDATE = 'ekyna_commerce.customer.update';
    const DELETE = 'ekyna_commerce.customer.delete';

    // Domain
    const INITIALIZE = 'ekyna_commerce.customer.initialize';

    const PRE_CREATE  = 'ekyna_commerce.customer.pre_create';
    const POST_CREATE = 'ekyna_commerce.customer.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.customer.pre_update';
    const POST_UPDATE = 'ekyna_commerce.customer.post_update';

    const PRE_DELETE  = 'ekyna_commerce.customer.pre_delete';
    const POST_DELETE = 'ekyna_commerce.customer.post_delete';

    const PARENT_CHANGE = 'ekyna_commerce.customer.parent_change';

    const BIRTHDAY               = 'ekyna_commerce.customer.birthday';
    const NEWSLETTER_SUBSCRIBE   = 'ekyna_commerce.customer.newsletter_subscribe';
    const NEWSLETTER_UNSUBSCRIBE = 'ekyna_commerce.customer.newsletter_unsubscribe';
}
