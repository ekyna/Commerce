<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockUnitAdjustmentInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAdjustmentInterface extends ResourceInterface
{
    public function getStockUnit(): ?StockUnitInterface;

    public function setStockUnit(?StockUnitInterface $stockUnit): StockAdjustmentInterface;

    public function getQuantity(): Decimal;

    /**
     * @return $this|StockAdjustmentInterface
     */
    public function setQuantity(Decimal $quantity): StockAdjustmentInterface;

    public function getReason(): string;

    /**
     * @return $this|StockAdjustmentInterface
     */
    public function setReason(string $reason): StockAdjustmentInterface;

    public function getNote(): ?string;

    /**
     * @return $this|StockAdjustmentInterface
     */
    public function setNote(?string $note): StockAdjustmentInterface;

    public function getCreatedAt(): DateTimeInterface;

    /**
     * @return $this|StockAdjustmentInterface
     */
    public function setCreatedAt(DateTimeInterface $createdAt): StockAdjustmentInterface;
}
