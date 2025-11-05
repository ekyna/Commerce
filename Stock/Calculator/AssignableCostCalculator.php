<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function is_null;
use function spl_object_id;

/**
 * Class AssignableCostCalculator
 * @package Ekyna\Component\Commerce\Stock\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AssignableCostCalculator implements AssignableCostCalculatorInterface
{
    private array $assignableCache = [];
    private array $subjectCache    = [];

    public function __construct(
        private readonly SubjectHelperInterface      $subjectHelper,
        private readonly SubjectCostGuesserInterface $subjectCostGuesser,
    ) {
    }

    public function onClear(): void
    {
        $this->assignableCache = [];
        $this->subjectCache = [];
    }

    public function calculateAssignableCost(AssignableInterface $item): Cost
    {
        $key = spl_object_id($item);

        if (isset($this->assignableCache[$key])) {
            return $this->assignableCache[$key];
        }

        if (!$item->hasStockAssignments()) {
            return $this->assignableCache[$key] = $this->calculateSubjectCost($item);
        }

        $result = new Cost();
        $qtySum = new Decimal(0);

        foreach ($item->getStockAssignments() as $assignment) {
            if (null === $cost = $this->getAssignmentCost($assignment)) {
                $result = $result->setAverage();

                $cost = $this->calculateSubjectCost($item);
            }

            $qtySum += $qty = $assignment->getSoldQuantity();

            $result = $result->add($cost->multiply($qty));
        }

        if ($qtySum->isZero()) {
            return $this->assignableCache[$key] = $this->calculateSubjectCost($item);
        }

        return $this->assignableCache[$key] = $result->divide($qtySum);
    }

    public function calculateSubjectCost(SubjectReferenceInterface $item): Cost
    {
        $key = spl_object_id($item);

        if (isset($this->subjectCache[$key])) {
            return $this->subjectCache[$key];
        }

        $default = $this->guessItemCost($item) ?? new Cost();
        $default = $default->setAverage();

        return $this->subjectCache[$key] = $default;
    }

    /**
     * Returns the stock assignment unit cost.
     */
    private function getAssignmentCost(AssignmentInterface $assignment): ?Cost
    {
        $unit = $assignment->getStockUnit();

        if (is_null($unit->getSupplierOrderItem()) && is_null($unit->getProductionOrder())) {
            return null;
        }

        return new Cost(
            product: $unit->getNetPrice(),
            supply: $unit->getShippingPrice()
        );
    }

    /**
     * Guesses the default subject unit cost.
     */
    protected function guessItemCost(SubjectReferenceInterface $item): ?Cost
    {
        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return null;
        }

        return $this->subjectCostGuesser->guess($subject);
    }
}
