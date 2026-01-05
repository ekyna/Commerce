<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Calculator;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Manufacture\Calculator\BillOfMaterialsCalculator;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectCostCalculator
 * @package Ekyna\Component\Commerce\Subject\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectCostCalculator implements SubjectCostCalculatorInterface
{
    public function __construct(
        private readonly SubjectCostGuesserInterface        $costGuesser,
        private readonly BillOfMaterialsRepositoryInterface $bomRepository,
        private readonly BillOfMaterialsCalculator          $bomCalculator,
    ) {
    }

    public function calculate(SubjectInterface $subject): ?Cost
    {
        if (null !== $cost = $this->costGuesser->guess($subject)) {
            return $cost;
        }

        if (null !== $bom = $this->bomRepository->findOneValidatedBySubject($subject)) {
            return $this->bomCalculator->calculateBOMCost($bom);
        }

        return null;
    }
}
