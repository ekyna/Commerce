<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Export;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\ProductionRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderShipmentItemRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Entity\StockSubjectLog;
use Ekyna\Component\Commerce\Stock\Helper\StockUnitHelper;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Ekyna\Component\Commerce\Stock\Model\StockLogTypeEnum;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierDeliveryItemRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Xls;
use Ekyna\Component\Resource\Model\DateRange;

use function sprintf;

/**
 * Class StockSubjectLogExporter
 * @package Ekyna\Component\Commerce\Stock\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectLogExporter
{
    public function __construct(
        private readonly SupplierDeliveryItemRepositoryInterface $supplierDeliveryItemRepository,
        private readonly ProductionRepositoryInterface           $productionRepository,
        private readonly OrderShipmentItemRepositoryInterface    $orderShipmentItemRepository,
        private readonly StockUnitHelper                         $stockUnitHelper,
    ) {
    }

    public function export(StockSubjectInterface $subject, ?DateRange $range): Xls
    {
        $list = $this->list($subject, $range);

        if ($range) {
            $filename = sprintf(
                '%s_%s_%s_log',
                $subject->getReference(),
                $range->getStart()->format('Y-m-d'),
                $range->getEnd()->format('Y-m-d')
            );
        } else {
            $filename = sprintf(
                '%s_log',
                $subject->getReference()
            );
        }

        $csv = new Xls($filename);

        foreach ($list as $log) {
            $csv->addRow([
                $log->date->format('Y-m-d'),
                $log->quantity->toFixed(),
                $log->type->name,
                $log->source,
            ]);
        }

        if (null === $range) {
            $total = new Decimal(0);

            foreach ($list as $log) {
                $total += $log->quantity;
            }

            $csv->addRow([]);
            $csv->addRow(['Total', $total->toFixed()]);
        }

        return $csv;
    }

    /**
     * @return array<int, StockSubjectLog>
     */
    public function list(StockSubjectInterface $subject, ?DateRange $range): array
    {
        $list = [];

        // Supplier deliveries logs
        $items = $this
            ->supplierDeliveryItemRepository
            ->findBySubjectAndDateRange($subject, $range);

        foreach ($items as $item) {
            $list[] = new StockSubjectLog(
                $subject,
                StockLogTypeEnum::SupplierDelivery,
                $item->getDelivery()->getCreatedAt(),
                $item->getSubjectQuantity(),
                $item->getDelivery()->getOrder()->getNumber()
            );
        }

        // Production (subject credits) logs
        $productions = $this
            ->productionRepository
            ->findBySubjectAndDateRange($subject, $range);

        foreach ($productions as $production) {
            $list[] = new StockSubjectLog(
                $subject,
                StockLogTypeEnum::ProductionSubject,
                $production->getCreatedAt(),
                new Decimal($production->getQuantity()),
                $production->getProductionOrder()->getNumber()
            );
        }

        // Production (component debits) logs
        $productions = $this
            ->productionRepository
            ->findByComponentAndDateRange($subject, $range);

        $identity = SubjectIdentity::fromSubject($subject);

        foreach ($productions as $production) {
            $item = $production
                ->getProductionOrder()
                ->getItems()
                ->filter(fn (ProductionItemInterface $item): bool => $item->getSubjectIdentity()->equals($identity))
                ->current();

            $list[] = new StockSubjectLog(
                $subject,
                StockLogTypeEnum::ProductionComponent,
                $production->getCreatedAt(),
                new Decimal(- $production->getQuantity() * $item->getQuantity()),
                $production->getProductionOrder()->getNumber()
            );
        }

        // Order shipments logs
        $items = $this
            ->orderShipmentItemRepository
            ->findBySubjectAndDateRange($subject, $range);

        foreach ($items as $item) {
            $shipment = $item->getShipment();

            $quantity = clone $item->getQuantity();
            if (!$shipment->isReturn()) {
                $quantity = $quantity->negate();
            }

            $list[] = new StockSubjectLog(
                $subject,
                StockLogTypeEnum::OrderShipment,
                $shipment->getShippedAt(),
                $quantity,
                $shipment->getOrder()->getNumber()
            );
        }

        $items = $this
            ->stockUnitHelper
            ->getRepository($subject)
            ->findAdjustmentsBySubjectAndDateRange($subject, $range);

        foreach ($items as $item) {
            $quantity = $item->getQuantity();

            if (StockAdjustmentReasons::isDebitReason($item)) {
                $quantity = $quantity->negate();
            }

            $list[] = new StockSubjectLog(
                $subject,
                StockLogTypeEnum::StockAdjustment,
                $item->getCreatedAt(),
                $quantity,
                $item->getReason() . ' ' . $item->getNote()
            );
        }

        usort($list, function (StockSubjectLog $a, StockSubjectLog $b) {
            return $a->date <=> $b->date;
        });

        return $list;
    }
}
