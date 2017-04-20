<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Event;

/**
 * Class CustomerGroupEvents
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerGroupEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.customer_group.insert';
    public const UPDATE      = 'ekyna_commerce.customer_group.update';
    public const DELETE      = 'ekyna_commerce.customer_group.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.customer_group.pre_create';
    public const POST_CREATE = 'ekyna_commerce.customer_group.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.customer_group.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.customer_group.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.customer_group.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.customer_group.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
