<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderAttachmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderAttachment
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderAttachment extends AbstractAttachment implements SupplierOrderAttachmentInterface
{
    /**
     * @var SupplierOrderInterface
     */
    protected $supplierOrder;


    /**
     * @inheritdoc
     */
    public function getSupplierOrder()
    {
        return $this->supplierOrder;
    }

    /**
     * @inheritdoc
     */
    public function setSupplierOrder(SupplierOrderInterface $order = null)
    {
        if ($order !== $this->supplierOrder) {
            if ($previous = $this->supplierOrder) {
                $this->supplierOrder = null;
                $previous->removeAttachment($this);
            }

            if ($this->supplierOrder = $order) {
                $this->supplierOrder->addAttachment($this);
            }
        }

        return $this;
    }
}
