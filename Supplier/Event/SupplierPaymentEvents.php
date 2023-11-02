<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierPaymentEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierPaymentEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.supplier_payment.insert';
    public const UPDATE      = 'ekyna_commerce.supplier_payment.update';
    public const DELETE      = 'ekyna_commerce.supplier_payment.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.supplier_payment.pre_create';
    public const POST_CREATE = 'ekyna_commerce.supplier_payment.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.supplier_payment.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.supplier_payment.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.supplier_payment.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.supplier_payment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
