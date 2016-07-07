<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class OrderValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($order, Constraint $constraint)
    {
        /* @var OrderInterface $order */
        /* @var Order $constraint */
        if ($order->requiresShipment() && (!$order->getSameAddress() && null === $order->getDeliveryAddress())) {
            $this->context->addViolationAt(
                'deliveryAddress',
                $constraint->deliveryAddressIsMandatory
            );
        }
    }
}
