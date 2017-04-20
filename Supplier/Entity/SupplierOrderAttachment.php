<?php

declare(strict_types=1);

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
    protected ?SupplierOrderInterface $supplierOrder = null;


    public function getSupplierOrder(): ?SupplierOrderInterface
    {
        return $this->supplierOrder;
    }

    public function setSupplierOrder(SupplierOrderInterface $order = null): SupplierOrderAttachmentInterface
    {
        if ($order === $this->supplierOrder) {
            return $this;
        }

        if ($previous = $this->supplierOrder) {
            $this->supplierOrder = null;
            $previous->removeAttachment($this);
        }

        if ($this->supplierOrder = $order) {
            $this->supplierOrder->addAttachment($this);
        }

        return $this;
    }
}
