<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\Availability;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

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
     * Constructor.
     *
     * @param Formatter $formatter
     */
    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
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
        $minQty = $maxQty = $aQty = $rQty = 0;
        $minMsg = $maxMsg = $aMsg = $rMsg = null;

        if ($subject->isQuoteOnly()) {
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
            } else{
                if (0 < $aQty = $subject->getAvailableStock()) {
                    $aMsg = $this->translate('in_stock', [
                        '%qty%' => $this->formatter->number($aQty),
                    ], $short);
                }
            }

            // Resupply quantity/message
            // TODO Only if stock mode === JUST_IN_TIME (?)
            if ((0 < $qty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
                $today = new \DateTime();
                $today->setTime(23, 59, 59);
                if ($today < $eda) {
                    $rQty = $qty;
                    $rMsg = $this->translate('pre_order', [
                        '%eda%' => $this->formatter->date($eda),
                        '%qty%' => $this->formatter->number($qty),
                    ], $short);
                }
            }

            $maxQty = $aQty + $rQty;

            // Overflow message
            if ($subject->isEndOfLife()) {
                $oMsg = $this->translate('end_of_life', [], $short);
            } elseif (0 < $days = $subject->getReplenishmentTime()) {
                $maxQty = INF;
                $oMsg = $this->translate('replenishment', [
                    '%days%' => $days,
                ], $short);
            } else {
                $oMsg = $this->translate('out_of_stock', [], $short);
            }

            if (0 < $maxQty && $maxQty !== INF) {
                $maxMsg = $this->translate('max_quantity', [
                    '%max%' => $this->formatter->number($minQty),
                ], $short);
            }
        }

        return new Availability($oMsg, $minQty, $minMsg, $maxQty, $maxMsg, $aQty, $aMsg, $rQty, $rMsg);
    }

    /**
     * @inheritdoc
     *
     * @TODO rename to "buyable quantity"
     */
    public function getAvailableQuantity(StockSubjectInterface $subject, $quantity = null)
    {
        if ($subject->isQuoteOnly()) {
            return 0;
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return INF;
        }

        if ($subject->getStockState() === StockSubjectStates::STATE_IN_STOCK) {
            return $subject->getAvailableStock();
        }

        // TODO Only if stock mode === JUST_IN_TIME (?)
        if ((0 < $qty = $subject->getVirtualStock()) && (null !== $subject->getEstimatedDateOfArrival())) {
            return $qty;
        }

        return 0;
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
            if (100 < $qty) {
                return $this->translate('available', [], $short);
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
