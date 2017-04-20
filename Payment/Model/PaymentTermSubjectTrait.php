<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Model;

/**
 * Trait PaymentTermSubjectTrait
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PaymentTermSubjectTrait
{
    protected ?PaymentTermInterface $paymentTerm = null;


    public function getPaymentTerm(): ?PaymentTermInterface
    {
        return $this->paymentTerm;
    }

    /**
     * @return $this|PaymentTermSubjectInterface
     */
    public function setPaymentTerm(?PaymentTermInterface $paymentTerm): PaymentTermSubjectInterface
    {
        $this->paymentTerm = $paymentTerm;

        return $this;
    }
}
