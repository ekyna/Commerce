<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\EventListener;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockAdjustmentListener
 * @package Ekyna\Component\Commerce\Stock\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentListener
{
    public function __construct(
        protected readonly PersistenceHelperInterface $persistenceHelper,
        protected readonly StockUnitUpdaterInterface  $stockUnitUpdater
    ) {

    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $stockAdjustment = $this->getStockAdjustmentFromEvent($event);

        if (null === $stockUnit = $stockAdjustment->getStockUnit()) {
            throw new RuntimeException('Stock unit must be set at this point.');
        }

        $quantity = StockAdjustmentReasons::isDebitReason($stockAdjustment->getReason())
            ? $stockAdjustment->getQuantity()->negate()
            : $stockAdjustment->getQuantity();

        $this->stockUnitUpdater->updateAdjusted($stockUnit, $quantity, true);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $stockAdjustment = $this->getStockAdjustmentFromEvent($event);

        if (null === $stockUnit = $stockAdjustment->getStockUnit()) {
            throw new RuntimeException('Stock unit must be set at this point.');
        }

        $cs = $this->persistenceHelper->getChangeSet($stockAdjustment);

        if (isset($cs['reason']) && ($cs['reason'][0] != $cs['reason'][1])) {
            // (Default) From DEBIT to CREDIT
            if (isset($cs['quantity'])) {
                $delta = Decimal::sum($cs['quantity']);
            } else {
                $delta = $stockAdjustment->getQuantity()->mul(2);
            }
            if (
                !StockAdjustmentReasons::isDebitReason($cs['reason'][0])
                && StockAdjustmentReasons::isDebitReason($cs['reason'][1])
            ) {
                // From CREDIT to DEBIT
                $delta = $delta->negate();
            }
            // TODO what if both old and new reasons are debit ?
        } elseif (isset($cs['quantity'])) {
            // NEW quantity - OLD quantity
            $delta = ($cs['quantity'][1] ?? new Decimal(0))->sub($cs['quantity'][0] ?? new Decimal(0));

            // Opposite delta if debit
            if (StockAdjustmentReasons::isDebitReason($stockAdjustment->getReason())) {
                $delta = $delta->negate();
            }
        } else {
            return;
        }

        $this->stockUnitUpdater->updateAdjusted($stockUnit, $delta, true);
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $stockAdjustment = $this->getStockAdjustmentFromEvent($event);

        if (null === $stockUnit = $stockAdjustment->getStockUnit()) {
            throw new RuntimeException('Stock unit must be set at this point.');
        }

        $quantity = StockAdjustmentReasons::isDebitReason($stockAdjustment->getReason())
            ? $stockAdjustment->getQuantity()
            : $stockAdjustment->getQuantity()->negate();

        $this->stockUnitUpdater->updateAdjusted($stockUnit, $quantity, true);
    }

    protected function getStockAdjustmentFromEvent(ResourceEventInterface $event): StockAdjustmentInterface
    {
        $stockAdjustment = $event->getResource();

        if (!$stockAdjustment instanceof StockAdjustmentInterface) {
            throw new UnexpectedTypeException($stockAdjustment, StockAdjustmentInterface::class);
        }

        return $stockAdjustment;
    }
}
