<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Stock\Assigner\AssignmentSupportTrait;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function max;
use function min;

/**
 * Class ProductionCalculator
 * @package Ekyna\Component\Commerce\Manufacture\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionCalculator
{
    use AssignmentSupportTrait;

    public function __construct(
        SubjectHelperInterface $subjectHelper,
    ) {
        $this->subjectHelper = $subjectHelper;
    }

    public function calculateMaxQuantity(ProductionInterface $current): int
    {
        $order = $current->getProductionOrder();

        // Limit by productions
        $limit = $order->getQuantity();
        foreach ($order->getProductions() as $production) {
            if ($production === $current) {
                continue;
            }

            $limit -= $production->getQuantity();
        }

        // Limit by assignments
        foreach ($order->getItems() as $item) {
            if (!$this->supportsAssignment($item)) {
                continue;
            }

            $shippable = new Decimal(0);
            foreach ($item->getStockAssignments() as $assignment) {
                $shippable = $shippable->add($assignment->getShippableQuantity());
            }

            $limit = min($limit, $shippable->div($item->getQuantity())->toInt());
        }

        if (null !== $current->getId()) {
            return max($limit, $current->getQuantity());
        }

        return $limit;
    }
}
