<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Common\View\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

/**
 * Class AvailabilityHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAvailabilityHelper
{
    /**
     * @var Formatter
     */
    protected $formatter;


    /**
     * Returns the formatter.
     *
     * @return Formatter
     */
    protected function getFormatter()
    {
        if (null !== $this->formatter) {
            return $this->formatter;
        }

        return $this->formatter = $this->formatter = new Formatter();
    }

    /**
     * Returns the subject's available quantity.
     *
     * @param StockSubjectInterface $subject
     *
     * @return float|int
     */
    public function getAvailableQuantity(StockSubjectInterface $subject)
    {
        if ($subject->isQuoteOnly()) {
            return 0;
        }

        // TODO MOQ
        if ($subject->getStockState() === StockSubjectStates::STATE_IN_STOCK) {
            return $subject->getAvailableStock();
        }

        // TODO MOQ
        if ((0 < $qty = $subject->getVirtualStock()) && (null !== $subject->getEstimatedDateOfArrival())) {
            return $qty;
        }

        return 0;
    }

    /**
     * Returns the subject's availability message.
     *
     * @param StockSubjectInterface $subject
     *
     * @return string
     */
    public function getAvailabilityMessage(StockSubjectInterface $subject)
    {
        if ($subject->isQuoteOnly()) {
            return $this->translate('quote_only');
        }

        // TODO MOQ
        if ($subject->getStockState() === StockSubjectStates::STATE_IN_STOCK) {
            return $this->translate('in_stock', [
                '%qty%' => $this->getFormatter()->number($subject->getAvailableStock()),
            ]);
        }

        // TODO MOQ
        if ((0 < $qty = $subject->getVirtualStock()) && (null !== $eda = $subject->getEstimatedDateOfArrival())) {
            return $this->translate('pre_order', [
                '%eda%' => $this->getFormatter()->date($eda),
                '%qty%' => $this->getFormatter()->number($qty),
            ]);
        }

        if (0 < $days = $subject->getReplenishmentTime()) {
            return $this->translate('replenishment', [
                '%days%' => $days,
            ]);
        }

        if ($subject->isEndOfLife()) {
            return $this->translate('end_of_life');
        }

        return $this->translate('out_of_stock');
    }

    /**
     * Translate the availability message.
     *
     * @param string $id
     * @param array  $parameters
     *
     * @return string
     */
    abstract protected function translate($id, array $parameters = []);
}
