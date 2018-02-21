<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

/**
 * Class SupplierOrderAttachmentTypes
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderAttachmentTypes
{
    const TYPE_FORM      = 'form';
    const TYPE_PROFORMA  = 'proforma';
    const TYPE_PAYMENT   = 'payment';
    const TYPE_FORWARDER = 'forwarder';
    const TYPE_IMPORT    = 'import';
    const TYPE_DELIVERY  = 'delivery';


    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}