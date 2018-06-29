<?php

namespace Ekyna\Component\Commerce\Accounting\Event;

/**
 * Class AccountingEvents
 * @package Ekyna\Component\Commerce\Accounting\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AccountingEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.accounting.insert';
    const UPDATE      = 'ekyna_commerce.accounting.update';
    const DELETE      = 'ekyna_commerce.accounting.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.accounting.initialize';

    const PRE_CREATE  = 'ekyna_commerce.accounting.pre_create';
    const POST_CREATE = 'ekyna_commerce.accounting.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.accounting.pre_update';
    const POST_UPDATE = 'ekyna_commerce.accounting.post_update';

    const PRE_DELETE  = 'ekyna_commerce.accounting.pre_delete';
    const POST_DELETE = 'ekyna_commerce.accounting.post_delete';
}
