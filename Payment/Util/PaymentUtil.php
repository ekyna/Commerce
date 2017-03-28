<?php

namespace Ekyna\Component\Commerce\Payment\Util;

use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;

/**
 * Class PaymentUtil
 * @package Ekyna\Component\Commerce\Payment\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentUtil
{
    /**
     * Calculate the outstanding date.
     *
     * @param PaymentTermInterface $term
     * @param \DateTime            $fromDate
     *
     * @return \DateTime The resulting outstanding date
     */
    static public function calculateOutstandingDate(PaymentTermInterface $term, \DateTime $fromDate)
    {
        $date = clone $fromDate;

        $date->modify(sprintf('+%s days', $term->getDays()));
        if ($term->getEndOfMonth()) {
            $date->modify('last day of this month');
        }

        $date->setTime(23, 59, 59);

        return $date;
    }
}
