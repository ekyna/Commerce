<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierProductEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierProductEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.supplier_product.insert';
    public const UPDATE      = 'ekyna_commerce.supplier_product.update';
    public const DELETE      = 'ekyna_commerce.supplier_product.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.supplier_product.pre_create';
    public const POST_CREATE = 'ekyna_commerce.supplier_product.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.supplier_product.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.supplier_product.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.supplier_product.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.supplier_product.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
