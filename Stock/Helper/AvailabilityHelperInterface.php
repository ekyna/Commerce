<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Common\View\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

/**
 * Interface AvailabilityHelperInterface
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AvailabilityHelperInterface
{
    /**
     * Returns the formatter.
     *
     * @return Formatter
     */
    public function getFormatter();

    /**
     * Returns the subject's available quantity.
     *
     * @param StockSubjectInterface $subject
     *
     * @return float|int
     */
    public function getAvailableQuantity(StockSubjectInterface $subject);

    /**
     * Returns the subject's availability message.
     *
     * @param StockSubjectInterface $subject
     *
     * @return string
     */
    public function getAvailabilityMessage(StockSubjectInterface $subject);

    /**
     * Translate the availability message.
     *
     * @param string $id
     * @param array  $parameters
     *
     * @return string
     */
    public function translate($id, array $parameters = []);
}
