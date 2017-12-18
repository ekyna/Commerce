<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Common\View\Formatter;
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
     * @var Formatter
     */
    protected $formatter;


    /**
     * @inheritdoc
     */
    public function getFormatter()
    {
        if (null !== $this->formatter) {
            return $this->formatter;
        }

        return $this->formatter = $this->formatter = new Formatter();
    }

    /**
     * @inheritdoc
     */
    public function getAvailableQuantity(StockSubjectInterface $subject)
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

        if ((0 < $qty = $subject->getVirtualStock()) && (null !== $subject->getEstimatedDateOfArrival())) {
            return $qty;
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getAvailabilityMessage(StockSubjectInterface $subject)
    {
        if ($subject->isQuoteOnly()) {
            return $this->translate('quote_only');
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return $this->translate('available');
        }

        if ($subject->getStockState() === StockSubjectStates::STATE_IN_STOCK) {
            return $this->translate('in_stock', [
                '%qty%' => $this->getFormatter()->number($subject->getAvailableStock()),
            ]);
        }

        if ((0 < $qty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
            return $this->translate('pre_order', [
                '%eda%' => $this->getFormatter()->date($eda),
                '%qty%' => $this->getFormatter()->number($qty),
            ]);
        }

        if ($subject->isEndOfLife()) {
            return $this->translate('end_of_life');
        }

        if (0 < $days = $subject->getReplenishmentTime()) {
            return $this->translate('replenishment', [
                '%days%' => $days,
            ]);
        }

        return $this->translate('out_of_stock');
    }
}