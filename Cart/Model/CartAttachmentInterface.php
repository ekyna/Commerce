<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;

/**
 * Interface CartAttachmentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartAttachmentInterface extends SaleAttachmentInterface
{
    public function getCart(): ?CartInterface;

    public function setCart(?CartInterface $cart): CartAttachmentInterface;
}
