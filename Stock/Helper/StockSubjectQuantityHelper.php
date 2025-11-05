<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Helper;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Stock\Assigner\AssignmentSupportTrait;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function array_key_exists;

/**
 * Class StockSubjectQuantityHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectQuantityHelper
{
    use AssignmentSupportTrait;

    private array $soldQuantityCache;

    public function __construct(
        private readonly InvoiceSubjectCalculatorInterface $invoiceCalculator,
        SubjectHelperInterface                             $subjectHelper,
    ) {
        $this->subjectHelper = $subjectHelper;
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

        if ($item instanceof AssignableInterface && $this->supportsAssignment($item)) {
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
}
