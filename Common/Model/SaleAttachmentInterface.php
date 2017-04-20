<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleAttachmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAttachmentInterface extends AttachmentInterface
{
    public function getSale(): ?SaleInterface;

    public function setSale(?SaleInterface $sale): SaleAttachmentInterface;
}
