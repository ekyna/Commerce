<?php

namespace Ekyna\Component\Commerce\Payment\Model;

/**
 * Trait PaymentTermSubjectTrait
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PaymentTermSubjectTrait
{
    /**
     * @var PaymentTermInterface
     */
    protected $paymentTerm;


    /**
     * Returns the payment term.
     *
     * @return PaymentTermInterface
     */
    public function getPaymentTerm()
    {
        return $this->paymentTerm;
    }

    /**
     * Sets the payment term.
     *
     * @param PaymentTermInterface $paymentTerm
     *
     * @return $this|PaymentTermSubjectInterface
     */
    public function setPaymentTerm(PaymentTermInterface $paymentTerm = null)
    {
        $this->paymentTerm = $paymentTerm;

        return $this;
    }
}
