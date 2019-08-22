<?php

namespace Ekyna\Component\Commerce\Payment\Factory;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Interface PaymentFactoryInterface
 * @package Ekyna\Component\Commerce\Payment\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentFactoryInterface
{
    /**
     * Creates a payment for the given subject.
     *
     * @param PaymentSubjectInterface $subject
     * @param PaymentMethodInterface  $method
     *
     * @return PaymentInterface
     */
    public function createPayment(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface;
}
