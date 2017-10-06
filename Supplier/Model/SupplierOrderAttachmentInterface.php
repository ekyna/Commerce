<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;

/**
 * Interface SupplierOrderAttachmentInterface
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderAttachmentInterface extends AttachmentInterface
{
    /**
     * Returns the supplier order.
     *
     * @return SupplierOrderInterface
     */
    public function getSupplierOrder();

    /**
     * Sets the supplier order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return SupplierOrderAttachmentInterface
     */
    public function setSupplierOrder(SupplierOrderInterface $order = null);
}
