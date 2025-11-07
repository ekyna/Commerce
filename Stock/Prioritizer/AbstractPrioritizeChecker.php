<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Stock\Assigner\AssignmentSupportTrait;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class AbstractPrioritizeChecker
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPrioritizeChecker
{
    use AssignmentSupportTrait;

    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    protected function checkAssignable(AssignableInterface $assignable): bool
    {
        $assignments = $assignable->getStockAssignments();

        if (0 === $assignments->count()) {
            return $this->supportsAssignment($assignable);
        }

        foreach ($assignments as $assignment) {
            if (!$assignment->isFullyShipped() && !$assignment->isFullyShippable()) {
                return true;
            }
        }

        return false;
    }
}
