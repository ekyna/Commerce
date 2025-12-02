<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionOrderCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ProductionOrderValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductionOrderCalculator $calculator
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ProductionOrderInterface) {
            throw new UnexpectedTypeException($value, ProductionOrderInterface::class);
        }
        if (!$constraint instanceof ProductionOrder) {
            throw new UnexpectedTypeException($constraint, ProductionOrder::class);
        }

        // BOM must be in a validated state
        if (BOMState::VALIDATED !== $value->getBom()->getState()) {
            $this
                ->context
                ->buildViolation('BOM must be in validated state.')
                ->atPath('productionOrder')
                ->addViolation();

            return;
        }

        // Order quantity can't be lower than produced quantity
        $this->validateQuantity($value);
    }

    private function validateQuantity(ProductionOrderInterface $order): void
    {
        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $min = $this->calculator->calculateProducedQuantity($order);

        $validator->atPath('quantity')->validate($order->getQuantity(), [
            new GreaterThanOrEqual(
                value: $min,
                message: 'La quantité déjà produite est de {{ compared_value }}. Veuillez saisir une valeur égale ou supérieure.'
            )
        ]);
    }
}
