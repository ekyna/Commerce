<?php

namespace Ekyna\Component\Commerce\Payment\Model;

/**
 * Interface PaymentTermSubjectInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentTermSubjectInterface
{
    /**
     * Returns the payment term.
     *
     * @return PaymentTermInterface
     */
    public function getPaymentTerm();

    /**
     * Sets the payment term.
     *
     * @param PaymentTermInterface $paymentTerm
     *
     * @return $this|PaymentTermSubjectInterface
     */
    public function setPaymentTerm(PaymentTermInterface $paymentTerm = null);
}
