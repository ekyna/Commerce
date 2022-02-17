<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Helper;

use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\Availability;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;

use const INF;

/**
 * Class AvailabilityHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAvailabilityHelper implements AvailabilityHelperInterface
{
    use FormatterAwareTrait;

    protected int $inStockLimit;

    public function __construct(FormatterFactory $formatterFactory, int $inStockLimit = 100)
    {
        $this->formatterFactory = $formatterFactory;
        $this->inStockLimit = $inStockLimit;
    }

    /**
     * Returns the subject availability for the given quantity.
     */
    public function getAvailability(
        StockSubjectInterface $subject,
        bool $root = true,
        bool $short = false
    ): Availability {
        $minQty = new Decimal(0);
        $aQty = new Decimal(0);
        $rQty = new Decimal(0);
        $maxQty = new Decimal(INF);
        $minMsg = $maxMsg = $aMsg = $rMsg = '';

        $today = new DateTime();
        $today->setTime(23, 59, 59, 999999);

        if ($root && $subject->isQuoteOnly()) {
            $maxQty = new Decimal(0);
            $oMsg = $maxMsg = $this->translate('quote_only', [], $short);
        } else {
            // Minimum quantity/message
            if ($root && (0 < $minQty = $subject->getMinimumOrderQuantity())) {
                $minMsg = $this->translate('min_quantity', [
                    '%min%' => $this->getFormatter()->number($minQty),
                ], $short);
            }

            // Available quantity/message
            if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                $aQty = new Decimal(INF);
                $aMsg = $this->translate('available', [], $short);
            } elseif ($minQty <= $aQty = $subject->getAvailableStock()) {
                $maxQty = $aQty;
                $aMsg = $this->translate('in_stock', [
                    '%qty%' => $this->formatQuantity($aQty),
                ], $short);
            } else {
                $maxQty = new Decimal(0);
            }

            // Resupply quantity/message
            if (
                ($eda = $subject->getEstimatedDateOfArrival())
                && ($today < $eda)
                && (0 < $vQty = $subject->getVirtualStock())
                && ($minQty <= $rQty = $vQty - $aQty)
            ) {
                $rMsg = $this->translate('pre_order', [
                    '%eda%' => $this->getFormatter()->date($eda),
                    '%qty%' => $this->formatQuantity($rQty),
                ], $short);
                $maxQty = $vQty;
            }

            // Overflow message
            if ($root && $subject->isEndOfLife()) {
                $oMsg = $this->translate('end_of_life', [], $short);
            } elseif (
                ($releasedAt = $subject->getReleasedAt()) && ($today < $releasedAt)
            ) {
                $oMsg = $this->translate('released_at', [
                    '%date%' => $this->getFormatter()->date($releasedAt),
                ], $short);
            } elseif (
                $subject->getStockMode() === StockSubjectModes::MODE_JUST_IN_TIME
                && 0 < $days = $subject->getReplenishmentTime()
            ) {
                $maxQty = new Decimal(INF);
                $oMsg = $this->translate('replenishment', [
                    '%days%' => $days,
                ], $short);
            } else {
                $oMsg = $this->translate('out_of_stock', [], $short);
            }

            if (0 < $maxQty && !$maxQty->equals(INF)) {
                $maxMsg = $this->translate('max_quantity', [
                    '%max%' => $this->getFormatter()->number($maxQty),
                ], $short);
            } elseif ($maxQty->isZero()) {
                $maxMsg = $oMsg;
            }
        }

        return new Availability($oMsg, $minQty, $minMsg, $maxQty, $maxMsg, $aQty, $aMsg, $rQty, $rMsg);
    }

    public function getAvailabilityMessage(
        StockSubjectInterface $subject,
        Decimal $quantity = null,
        bool $root = true,
        bool $short = false
    ): string {
        if (is_null($quantity)) {
            $quantity = $root ? $subject->getMinimumOrderQuantity() : new Decimal(0);
        }

        if ($root && $subject->isQuoteOnly()) {
            return $this->translate('quote_only', [], $short);
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return $this->translate('available', [], $short);
        }

        if ($quantity <= $qty = $subject->getAvailableStock()) {
            return $this->translate('in_stock', [
                '%qty%' => $this->formatQuantity($qty)
            ], $short);
        }

        $today = new DateTime();
        $today->setTime(23, 59, 59, 999999);

        if (
            ($quantity <= $qty = $subject->getVirtualStock())
            && ($eda = $subject->getEstimatedDateOfArrival())
            && ($today < $eda)
        ) {
            return $this->translate('pre_order', [
                '%eda%' => $this->getFormatter()->date($eda),
                '%qty%' => $this->formatQuantity($qty),
            ], $short);
        }

        if ($root && $subject->isEndOfLife()) {
            return $this->translate('end_of_life', [], $short);
        }

        if (
            ($releasedAt = $subject->getReleasedAt())
            && ($today < $releasedAt)
        ) {
            return $this->translate('released_at', [
                '%date%' => $this->getFormatter()->date($releasedAt),
            ], $short);
        }

        if (
            $subject->getStockMode() === StockSubjectModes::MODE_JUST_IN_TIME
            && 0 < $days = $subject->getReplenishmentTime()
        ) {
            return $this->translate('replenishment', [
                '%days%' => $days,
            ], $short);
        }

        return $this->translate('out_of_stock', [], $short);
    }

    /**
     * Formats the given quantity.
     */
    protected function formatQuantity(Decimal $qty): string
    {
        if (0 < $this->inStockLimit && $this->inStockLimit < $qty) {
            return $this->inStockLimit . '+';
        }

        return $this->getFormatter()->number($qty);
    }

    /**
     * Translate the availability message.
     */
    abstract protected function translate(string $id, array $parameters = [], bool $short = false): string;
}
