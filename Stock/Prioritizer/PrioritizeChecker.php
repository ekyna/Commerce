<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Assigner\AssignmentSupportTrait;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class PrioritizeChecker
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PrioritizeChecker implements PrioritizeCheckerInterface
{
    use SaleCheckerTrait;
    use AssignmentSupportTrait;

    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function canPrioritizeSale(SaleInterface $sale): bool
    {
        if (!$this->checkSale($sale)) {
            return false;
        }

        foreach ($sale->getItems() as $item) {
            if ($this->can($item, false)) {
                return true;
            }
        }

        return false;
    }

    public function canPrioritizeSaleItem(SaleItemInterface $item): bool
    {
        return $this->can($item, true);
    }

    protected function can(SaleItemInterface $item, bool $checkSale): bool
    {
        if ($checkSale && !$this->checkSale($item->getRootSale())) {
            return false;
        }

        foreach ($item->getChildren() as $child) {
            if ($this->can($child, false)) {
                return true;
            }
        }

        if (!$item instanceof StockAssignmentsInterface) {
            return false;
        }

        $assignments = $item->getStockAssignments();

        if (0 === $assignments->count()) {
            return $this->supportsAssignment($item);
        }

        foreach ($assignments as $assignment) {
            if (!$assignment->isFullyShipped() && !$assignment->isFullyShippable()) {
                return true;
            }
        }

        return false;
    }
}
