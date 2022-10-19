<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Helper;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function array_key_exists;

/**
 * Class StockSubjectQuantityHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectQuantityHelper
{
    private array $soldQuantityCache;

    public function __construct(
        private readonly SubjectHelperInterface $subjectHelper,
        private readonly InvoiceSubjectCalculatorInterface $invoiceCalculator
    ) {
        $this->clear();
    }

    public function clear(): void
    {
        $this->soldQuantityCache = [];
    }

    public function calculateSoldQuantity(SaleItemInterface $item): Decimal
    {
        $id = $item->getId();
        if (array_key_exists($id, $this->soldQuantityCache)) {
            return $this->soldQuantityCache[$id];
        }

        if ($item->isCompound()) {
            $quantity = $item->getTotalQuantity();

            foreach ($item->getChildren() as $child) {
                $quantity = min(
                    $quantity,
                    $this->calculateSoldQuantity($child)->div($child->getQuantity())
                );
            }

            return $this->soldQuantityCache[$id] = $quantity;
        }

        if ($this->supportsAssignment($item)) {
            /** @var StockAssignmentsInterface $item */
            $soldTotal = new Decimal(0);
            foreach ($item->getStockAssignments() as $assignment) {
                $soldTotal = $soldTotal->add($assignment->getSoldQuantity());
            }

            return $this->soldQuantityCache[$id] = $soldTotal;
        }

        if ($item->getRootSale() instanceof InvoiceSubjectInterface) {
            return $this->invoiceCalculator->calculateSoldQuantity($item);
        }

        return $this->soldQuantityCache[$id] = $item->getTotalQuantity();
    }

    /**
     * Returns whether the given item supports assignments.
     *
     * @param SaleItemInterface $item
     *
     * @return bool
     */
    public function supportsAssignment(SaleItemInterface $item): bool
    {
        if ($item->isCompound()) {
            return false;
        }

        if (!$item instanceof StockAssignmentsInterface) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
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
