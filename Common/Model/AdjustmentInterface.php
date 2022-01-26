<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface AdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentInterface extends ResourceModel\ResourceInterface, ResourceModel\SortableInterface
{
    // TODO Move designation to SaleAdjustmentInterface (not needed elsewhere)
    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): AdjustmentInterface;

    public function getType(): string;

    public function setType(string $type): AdjustmentInterface;

    public function getMode(): string;

    public function setMode(string $mode): AdjustmentInterface;

    public function getAmount(): Decimal;

    public function setAmount(Decimal $amount): AdjustmentInterface;

    public function isImmutable(): bool;

    public function setImmutable(bool $immutable): AdjustmentInterface;

    public function getSource(): ?string;

    public function setSource(?string $source): AdjustmentInterface;

    /**
     * Returns whether this adjustment equals the given adjustment.
     */
    public function equals(AdjustmentInterface $adjustment): bool;

    public function getAdjustable(): ?AdjustableInterface;
}
