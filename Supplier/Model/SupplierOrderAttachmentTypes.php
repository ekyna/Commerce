<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

/**
 * Class SupplierOrderAttachmentTypes
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderAttachmentTypes
{
    public const TYPE_QUOTE     = 'quote';
    public const TYPE_FORM      = 'form';
    public const TYPE_INVOICE   = 'invoice';
    public const TYPE_PROFORMA  = 'proforma';
    public const TYPE_PAYMENT   = 'payment';
    public const TYPE_FORWARDER = 'forwarder';
    public const TYPE_IMPORT    = 'import';
    public const TYPE_DELIVERY  = 'delivery';

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
