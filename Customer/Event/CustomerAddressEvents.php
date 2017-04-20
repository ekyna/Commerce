<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Event;

/**
 * Class CustomerAddressEvents
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerAddressEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.customer_address.insert';
    public const UPDATE      = 'ekyna_commerce.customer_address.update';
    public const DELETE      = 'ekyna_commerce.customer_address.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.customer_address.pre_create';
    public const POST_CREATE = 'ekyna_commerce.customer_address.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.customer_address.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.customer_address.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.customer_address.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.customer_address.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
