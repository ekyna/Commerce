<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\Availability;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;

/**
 * Class AvailabilityHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAvailabilityHelper implements AvailabilityHelperInterface
{
    /**
     * @var \Ekyna\Component\Commerce\Common\Util\Formatter
     */
    protected $formatter;

    /**
     * @var int
     */
    protected $inStockLimit;

    /**
     * Constructor.
     *
     * @param Formatter $formatter
     * @param int       $inStockLimit
     */
    public function __construct(Formatter $formatter, $inStockLimit = 100)
    {
        $this->formatter = $formatter;
        $this->inStockLimit = $inStockLimit;
    }

    /**
     * Returns the subject availability for the given quantity.
     *
     * @param StockSubjectInterface $subject
     * @param bool                  $short
     *
     * @return Availability
     */
    public function getAvailability(StockSubjectInterface $subject, bool $short = false)
    {
        $minQty = $aQty = $rQty = 0;
        $maxQty = INF;
        $minMsg = $maxMsg = $aMsg = $rMsg = null;

        if ($subject->isQuoteOnly()) {
            $maxQty = 0;
            $oMsg = $maxMsg = $this->translate('quote_only', [], $short);
        } else {
            // Minimum quantity/message
            if (0 < $minQty = $subject->getMinimumOrderQuantity()) {
                $minMsg = $this->translate('min_quantity', [
                    '%min%' => $this->formatter->number($minQty),
                ], $short);
            }

            // Available quantity/message
            if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                $aQty = INF;
                $aMsg = $this->translate('available', [], $short);
            } elseif (0 < $aQty = $subject->getAvailableStock()) {
                $maxQty = $aQty;
                $aMsg = $this->translate('in_stock', [
                    '%qty%' => $this->formatter->number($aQty),
                ], $short);
            }


            // Resupply quantity/message
            if ((0 < $qty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
                $today = new \DateTime();
                $today->setTime(23, 59, 59);
                if ($today < $eda && 0 < $rQty = $qty - $aQty) {
                    $rMsg = $this->translate('pre_order', [
                        '%eda%' => $this->formatter->date($eda),
                        '%qty%' => $this->formatter->number($qty),
                    ], $short);
                    $maxQty = $qty;
                }
            }

            // Overflow message
            if ($subject->isEndOfLife()) {
                $oMsg = $this->translate('end_of_life', [], $short);
            } elseif (
                $subject->getStockMode() === StockSubjectModes::MODE_JUST_IN_TIME &&
                0 < $days = $subject->getReplenishmentTime()
            ) {
                $maxQty = INF;
                $oMsg = $this->translate('replenishment', [
                    '%days%' => $days,
                ], $short);
            } else {
                $oMsg = $this->translate('out_of_stock', [], $short);
            }

            if (0 < $maxQty && $maxQty !== INF) {
                $maxMsg = $this->translate('max_quantity', [
                    '%max%' => $this->formatter->number($maxQty),
                ], $short);
            }
        }

        return new Availability($oMsg, $minQty, $minMsg, $maxQty, $maxMsg, $aQty, $aMsg, $rQty, $rMsg);
    }

    /**
     * @inheritdoc
     */
    public function getAvailabilityMessage(StockSubjectInterface $subject, $quantity = null, $short = false)
    {
        if (0 >= $quantity) {
            $quantity = 0;
        }

        if ($subject->isQuoteOnly()) {
            return $this->translate('quote_only', [], $short);
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return $this->translate('available', [], $short);
        }

        if ($quantity < $qty = $subject->getAvailableStock()) {
            if (0 < $this->inStockLimit && $this->inStockLimit < $qty) {
                return $this->translate('in_stock', [
                    '%qty%' => $this->inStockLimit . '+',
                ], $short);
            }

            return $this->translate('in_stock', [
                '%qty%' => $this->formatter->number($qty),
            ], $short);
        }

        // TODO Only if stock mode === JUST_IN_TIME (?)
        if (($quantity < $qty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
            $today = new \DateTime();
            $today->setTime(23, 59, 59);
            if ($today < $eda) {
                return $this->translate('pre_order', [
                    '%eda%' => $this->formatter->date($eda),
                    '%qty%' => $this->formatter->number($qty),
                ], $short);
            }

            return $this->translate('replenishment', [
                '%days%' => $subject->getReplenishmentTime() ?: 20,
            ], $short);
        }

        if ($subject->isEndOfLife()) {
            return $this->translate('end_of_life', [], $short);
        }

        if (0 < $days = $subject->getReplenishmentTime()) {
            return $this->translate('replenishment', [
                '%days%' => $days,
            ], $short);
        }

        return $this->translate('out_of_stock', [], $short);
    }
}
