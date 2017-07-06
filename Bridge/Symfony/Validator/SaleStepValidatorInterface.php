<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;


/**
 * Interface SaleStepValidatorInterface
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleStepValidatorInterface
{
    const SHIPMENT_STEP = 'shipment';
    const PAYMENT_STEP  = 'payment';


    /**
     * Returns whether the cart is valid for the given step.
     *
     * @param SaleInterface $sale
     * @param string        $step
     *
     * @return bool
     */
    public function validate(SaleInterface $sale, $step);

    /**
     * Returns the violation list.
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface|null
     */
    public function getViolationList();
}
