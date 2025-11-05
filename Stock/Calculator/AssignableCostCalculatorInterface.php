<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Calculator;


use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;

/**
 * Class AssignableCostCalculator
 * @package Ekyna\Component\Commerce\Stock\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AssignableCostCalculatorInterface
{
    /**
     * Calculates the assignable unit cost.
     *
     * @param AssignableInterface $item
     * @return Cost
     */
    public function calculateAssignableCost(AssignableInterface $item): Cost;

    /**
     * Calculates the subject unit cost.
     *
     * @param SubjectReferenceInterface $item
     * @return Cost
     */
    public function calculateSubjectCost(SubjectReferenceInterface $item): Cost;
}
