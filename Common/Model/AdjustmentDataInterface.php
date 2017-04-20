<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;

/**
 * Interface AdjustmentDataInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentDataInterface
{
    public function getDesignation(): string;

    public function getMode(): string;

    public function getAmount(): Decimal;

    public function getSource(): ?string;

    public function isImmutable(): bool;
}
