<?php

namespace Ekyna\Component\Commerce\Payment\Util;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
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

    /**
     * Returns whether the payment can be cancelled by the user.
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    static public function isUserCancellable(PaymentInterface $payment)
    {
        if (null === $method = $payment->getMethod()) {
            return false;
        }

        if ($payment->getState() === PaymentStates::STATE_CANCELLED) {
            return false;
        }

        if ($method->isOutstanding()) {
            return false;
        }

        if ($method->isCredit()) {
            return true;
        }

        if ($method->isManual() && $payment->getState() === PaymentStates::STATE_PENDING) {
            return true;
        }

        return false;
    }
}
