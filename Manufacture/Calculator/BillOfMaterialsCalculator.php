<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Calculator;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMComponentInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class BillOfMaterialsCalculator
 * @package Ekyna\Component\Commerce\Manufacture\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsCalculator
{
    public function __construct(
        private readonly SubjectHelperInterface             $subjectHelper,
        private readonly SubjectCostGuesserInterface        $subjectCostGuesser,
        private readonly BillOfMaterialsRepositoryInterface $bomRepository
    ) {
    }

    public function calculateBOMCost(BillOfMaterialsInterface $bom): Cost
    {
        $total = new Cost(average: true);
        foreach ($bom->getComponents() as $component) {
            $total = $total->add(
                $this->calculateComponentTotalCost($component)
            );
        }

        return $total;
    }

    public function calculateComponentTotalCost(BOMComponentInterface $component): Cost
    {
        if (null === $subject = $this->subjectHelper->resolve($component, false)) {
            return new Cost(average: true);
        }

        $cost = $this
            ->subjectCostGuesser
            ->guess($subject);

        if (null === $cost) {
            $bom = $this->bomRepository->findOneValidatedBySubject($component);
            if (null !== $bom) {
                $cost = $this->calculateBOMCost($bom);
            }
        }

        if (null !== $cost) {
            return $cost->multiply($component->getQuantity());
        }

        return new Cost(average: true);
    }
}
