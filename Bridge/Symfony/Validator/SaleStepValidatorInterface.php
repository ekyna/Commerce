<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Interface SaleStepValidatorInterface
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleStepValidatorInterface
{
    public const CHECKOUT_STEP = 'checkout';
    public const SHIPMENT_STEP = 'shipment';
    public const PAYMENT_STEP  = 'payment';

    /**
     * Returns whether the cart is valid for the given step.
     */
    public function validate(SaleInterface $sale, string $step): bool;

    /**
     * Returns the violation list.
     *
     * @return ConstraintViolationListInterface|ConstraintViolationInterface[]|null
     */
    public function getViolationList();
}
