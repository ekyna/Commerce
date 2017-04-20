<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;

/**
 * Interface SupplierOrderAttachmentInterface
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderAttachmentInterface extends AttachmentInterface
{
    public function getSupplierOrder(): ?SupplierOrderInterface;

    public function setSupplierOrder(SupplierOrderInterface $order = null): SupplierOrderAttachmentInterface;
}
