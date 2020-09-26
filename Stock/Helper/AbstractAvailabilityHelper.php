<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
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
    use FormatterAwareTrait;

    /**
     * @var int
     */
    protected $inStockLimit;


    /**
     * Constructor.
     *
     * @param FormatterFactory $formatterFactory
     * @param int              $inStockLimit
     */
    public function __construct(FormatterFactory $formatterFactory, $inStockLimit = 100)
    {
        $this->formatterFactory = $formatterFactory;
        $this->inStockLimit = $inStockLimit;
    }

    /**
     * Returns the subject availability for the given quantity.
     *
     * @param StockSubjectInterface $subject
     * @param bool                  $root
     * @param bool                  $short
     *
     * @return Availability
     */
    public function getAvailability(
        StockSubjectInterface $subject,
        bool $root = true,
        bool $short = false
    ): Availability {
        $minQty = $aQty = $rQty = 0;
        $maxQty = INF;
        $minMsg = $maxMsg = $aMsg = $rMsg = null;

        if ($root && $subject->isQuoteOnly()) {
            $maxQty = 0;
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
                $aQty = INF;
                $aMsg = $this->translate('available', [], $short);
            } elseif ($minQty <= $aQty = $subject->getAvailableStock()) {
                $maxQty = $aQty;
                $aMsg = $this->translate('in_stock', [
                    '%qty%' => $this->formatQuantity($aQty),
                ], $short);
            } else {
                $maxQty = 0;
            }

            // Resupply quantity/message
            if ((0 < $vQty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
                $today = new \DateTime();
                $today->setTime(23, 59, 59, 999999);
                if (($today < $eda) && ($minQty <= $rQty = $vQty - $aQty)) {
                    $rMsg = $this->translate('pre_order', [
                        '%eda%' => $this->getFormatter()->date($eda),
                        '%qty%' => $this->formatQuantity($rQty),
                    ], $short);
                    $maxQty = $vQty;
                }
            }

            // Overflow message
            if ($root && $subject->isEndOfLife()) {
                $oMsg = $this->translate('end_of_life', [], $short);
            } elseif (
                $subject->getStockMode() === StockSubjectModes::MODE_JUST_IN_TIME
                && 0 < $days = $subject->getReplenishmentTime()
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
                    '%max%' => $this->getFormatter()->number($maxQty),
                ], $short);
            } elseif (0 == $maxQty) {
                $maxMsg = $oMsg;
            }
        }

        return new Availability($oMsg, $minQty, $minMsg, $maxQty, $maxMsg, $aQty, $aMsg, $rQty, $rMsg);
    }

    /**
     * @inheritdoc
     */
    public function getAvailabilityMessage(
        StockSubjectInterface $subject,
        float $quantity = null,
        bool $root = true,
        bool $short = false
    ): string {
        if (is_null($quantity)) {
            $quantity = $root ? $subject->getMinimumOrderQuantity() : 0;
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

        // TODO Only if stock mode === JUST_IN_TIME (?)
        if (
            ($quantity <= $qty = $subject->getVirtualStock())
            && (null !== $eda = $subject->getEstimatedDateOfArrival())
        ) {
            $today = new \DateTime();
            $today->setTime(23, 59, 59, 999999);
            if ($today < $eda) {
                return $this->translate('pre_order', [
                    '%eda%' => $this->getFormatter()->date($eda),
                    '%qty%' => $this->formatQuantity($qty),
                ], $short);
            }
        }

        if ($root && $subject->isEndOfLife()) {
            return $this->translate('end_of_life', [], $short);
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
     *
     * @param float $qty
     *
     * @return string
     */
    protected function formatQuantity(float $qty): string
    {
        if (0 < $this->inStockLimit && $this->inStockLimit < $qty) {
            return $this->inStockLimit . '+';
        }

        return $this->getFormatter()->number($qty);
    }

    /**
     * Translate the availability message.
     *
     * @param string $id
     * @param array  $parameters
     * @param bool   $short
     *
     * @return string
     */
    abstract protected function translate(string $id, array $parameters = [], $short = false): string;
}
