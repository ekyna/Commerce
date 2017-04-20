<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Event;

/**
 * Class CustomerEvents
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerEvents
{
    // Persistence
    public const INSERT = 'ekyna_commerce.customer.insert';
    public const UPDATE = 'ekyna_commerce.customer.update';
    public const DELETE = 'ekyna_commerce.customer.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.customer.pre_create';
    public const POST_CREATE = 'ekyna_commerce.customer.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.customer.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.customer.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.customer.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.customer.post_delete';

    public const PARENT_CHANGE = 'ekyna_commerce.customer.parent_change';

    public const BIRTHDAY               = 'ekyna_commerce.customer.birthday';
    public const NEWSLETTER_SUBSCRIBE   = 'ekyna_commerce.customer.newsletter_subscribe';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
