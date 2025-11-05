<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Trait AssignmentSupportTrait
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait AssignmentSupportTrait
{
    protected readonly SubjectHelperInterface $subjectHelper;

    /**
     * Returns whether the given item supports assignments.
     *
     * @param AssignableInterface $assignable
     *
     * @return bool
     */
    public function supportsAssignment(AssignableInterface $assignable): bool
    {
        // TODO Check if sale is in stockable state

        if ($assignable instanceof SaleItemInterface && $assignable->isCompound()) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($assignable, false)) {
            return false;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return false;
        }

        if ($subject->isStockCompound()) {
            return false;
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return false;
        }

        return true;
    }
}
