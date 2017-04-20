<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use DateTimeInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface CartInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartInterface extends SaleInterface
{
    public function getExpiresAt(): ?DateTimeInterface;

    public function setExpiresAt(?DateTimeInterface $expiresAt): CartInterface;
}
