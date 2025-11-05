<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ProductionValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductionCalculator $calculator,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ProductionInterface) {
            throw new UnexpectedTypeException($value, ProductionInterface::class);
        }
        if (!$constraint instanceof Production) {
            throw new UnexpectedTypeException($constraint, Production::class);
        }

        $this->validateOrderState($value);
        $this->validateQuantity($value);
    }

    private function validateOrderState(ProductionInterface $production): void
    {
        if (POState::isStockableState($production->getProductionOrder())) {
            return;
        }

        $this
            ->context
            ->buildViolation('Order must be in stockable state.')
            ->atPath('productionOrder')
            ->addViolation();
    }

    private function validateQuantity(ProductionInterface $production): void
    {
        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $max = $this->calculator->calculateMaxQuantity($production);

        $validator->atPath('quantity')->validate($production->getQuantity(), [
            new LessThanOrEqual(
                value: $max,
                message: 'La quantité maximum pouvant être produite est de {{ compared_value }}'
            )
        ]);
    }
}
