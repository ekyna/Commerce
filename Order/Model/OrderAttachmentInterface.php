<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;

/**
 * Interface OrderAttachmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderAttachmentInterface extends SaleAttachmentInterface
{
    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): OrderAttachmentInterface;
}
