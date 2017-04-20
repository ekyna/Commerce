<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Accounting\Event;

/**
 * Class AccountingEvents
 * @package Ekyna\Component\Commerce\Accounting\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AccountingEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.accounting.insert';
    public const UPDATE      = 'ekyna_commerce.accounting.update';
    public const DELETE      = 'ekyna_commerce.accounting.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.accounting.pre_create';
    public const POST_CREATE = 'ekyna_commerce.accounting.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.accounting.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.accounting.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.accounting.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.accounting.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
