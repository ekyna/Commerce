<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Calculator;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectCostCalculator
 * @package Ekyna\Component\Commerce\Subject\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectCostCalculatorInterface
{
    public function calculate(SubjectInterface $subject): ?Cost;
}
