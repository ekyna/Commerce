<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Model;

/**
 * Interface PaymentTermSubjectInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentTermSubjectInterface
{
    public function getPaymentTerm(): ?PaymentTermInterface;

    public function setPaymentTerm(?PaymentTermInterface $paymentTerm): PaymentTermSubjectInterface;
}
