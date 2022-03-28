<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAvailability;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class AvailabilityResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityResolver
{
    private ShipmentSubjectCalculatorInterface $calculator;
    private SubjectHelperInterface             $subjectHelper;

    private ?ShipmentInterface $shipment;
    /** @var array<int, ShipmentAvailability> */
    private array $cache;

    public function __construct(
        ShipmentSubjectCalculatorInterface $calculator,
        SubjectHelperInterface             $subjectHelper,
        ?ShipmentInterface                 $shipment
    ) {
        $this->calculator = $calculator;
        $this->subjectHelper = $subjectHelper;
        $this->shipment = $shipment;
        $this->cache = [];
    }

    public function resolveSaleItem(SaleItemInterface $item): ShipmentAvailability
    {
        if (isset($this->cache[$item->getId()])) {
            return $this->cache[$item->getId()];
        }

        $this->calculateSale($item->getSale());

        return $this->cache[$item->getId()];
    }

    private function calculateSale(SaleInterface $sale): void
    {
        foreach ($sale->getItems() as $item) {
            $this->calculateSaleItem($item);
        }
    }

    private function calculateSaleItem(SaleItemInterface $item): ShipmentAvailability
    {
        $inStock = null;

        if ($item->isCompound()) {
            $expected = new Decimal(INF);
            $assigned = new Decimal(INF);
            $inStock = new Decimal(INF);
        } else {
            $isReturn = $this->shipment && $this->shipment->isReturn();

            if ($isReturn) {
                $expected = $assigned = $this
                    ->calculator
                    ->calculateReturnableQuantity($item, $this->shipment);
            } else {
                $expected = $this
                    ->calculator
                    ->calculateShippableQuantity($item, $this->shipment);

                $assigned = $this
                    ->calculator
                    ->calculateAvailableQuantity($item, $this->shipment);

                if ($assigned < $expected) {
                    $subject = $this->subjectHelper->resolve($item);
                    if ($subject instanceof StockSubjectInterface) {
                        $inStock = $subject->getInStock();
                    }
                }
            }
        }

        $availability = new ShipmentAvailability($item, $expected, $assigned, $inStock);

        foreach ($item->getChildren() as $child) {
            $childAvailability = $this->calculateSaleItem($child);

            $availability->addChild($childAvailability);

            if (!$child->isPrivate()) {
                continue;
            }

            $e = $childAvailability->getExpected()->div($child->getQuantity());
            if ($expected > $e) {
                $expected = $e;
            }

            $a = $childAvailability->getAssigned()->div($child->getQuantity());
            if ($assigned > $a) {
                $assigned = $a;
            }

            if ($i = $childAvailability->getInStock()) {
                $i = $i->div($child->getQuantity());
                if (null === $inStock || $inStock > $i) {
                    $inStock = $i;
                }
            }
        }

        $spread = $item->hasPrivateChildren();

        $availability
            ->setExpected($expected, $spread)
            ->setAssigned($assigned, $spread)
            ->setInStock($inStock);

        return $this->cache[$item->getId()] = $availability;
    }
}
