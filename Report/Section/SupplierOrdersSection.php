<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Section\Model\SupplierData;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Ekyna\Component\Commerce\Stock\Helper\StockSubjectQuantityHelper;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Helper\File\Xls;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_walk;
use function Symfony\Component\Translation\t;
use function uasort;

/**
 * Class SupplierOrdersSection
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrdersSection implements SectionInterface
{
    final public const NAME = 'supplier_orders';

    /** @var array<int, array<int, SupplierData>> */
    private array $data;
    /** @var array<int, string> */
    private array $names;
    /** @var array<int, string> */
    private array  $years;
    private string $year;

    private ?MarginCalculatorInterface $calculator = null;

    public function __construct(
        private readonly StockSubjectQuantityHelper $helper,
        private readonly MarginCalculatorFactory    $factory,
    ) {
    }

    public function initialize(ReportConfig $config): void
    {
        $this->data = [];
        $this->names = [];
        $this->years = $config->range->getYears();
    }

    /**
     * @inheritDoc
     */
    public function read(ResourceInterface $resource): void
    {
        if ($resource instanceof OrderInterface) {
            $this->readOrder($resource);

            return;
        }

        if ($resource instanceof SupplierOrderInterface) {
            $this->readSupplierOrder($resource);

            return;
        }

        throw new UnexpectedTypeException($resource, [OrderInterface::class, SupplierOrderInterface::class]);
    }

    public function readOrder(OrderInterface $order): void
    {
        $this->year = $order->getAcceptedAt()->format('Y');

        $this->calculator = $this->factory->create();

        $this->calculateOrderItems($order->getItems());
    }

    private function calculateOrderItems(Collection $items): void
    {
        foreach ($items as $item) {
            $this->calculateOrderItem($item);

            $this->calculateOrderItems($item->getChildren());
        }
    }

    private function calculateOrderItem(OrderItemInterface $item): void
    {
        if ($item->isCompound()) {
            return;
        }

        $soldTotal = $this->helper->calculateSoldQuantity($item);
        if ($soldTotal->isZero()) {
            return;
        }

        $grossMargin = $this->calculator->calculateSaleItem($item, true);

        foreach ($item->getStockAssignments() as $assignment) {
            $unit = $assignment->getStockUnit();
            if (null === $supplier = $unit->getSupplierOrder()?->getSupplier()) {
                // TODO Should never happen (but a lot exists T_T)
                continue;
            }

            $id = $supplier->getId();

            if (!isset($this->names[$id])) {
                $this->names[$id] = $supplier->getName();
            }
            if (!isset($this->data[$id][$this->year])) {
                $this->data[$id][$this->year] = new SupplierData();
            }

            $data = $this->data[$id][$this->year];

            $sold = $assignment->getSoldQuantity();
            $goodCost = $unit->getNetPrice()->mul($sold);
            $supplyCost = $unit->getShippingPrice()->mul($sold);

            $data->saleGoodCost += $goodCost;
            $data->saleSupplyCost += $supplyCost;          // TODO Should use total quantity (need Assignment::credited quantity)
            $data->saleRevenue += $grossMargin->getRevenueProduct()->mul($sold)->div($soldTotal)->round(2);
        }
    }

    public function readSupplierOrder(SupplierOrderInterface $order): void
    {
        if (null === $supplier = $order->getSupplier()) {
            // TODO Should never happen
            return;
        }

        $id = $supplier->getId();
        $this->year = $order->getOrderedAt()->format('Y');

        if (!isset($this->names[$id])) {
            $this->names[$id] = $supplier->getName();
        }
        if (!isset($this->data[$id][$this->year])) {
            $this->data[$id][$this->year] = new SupplierData();
        }

        $total = $order->getPaymentTotal() - $order->getTaxTotal() - $order->getShippingCost();

        $this->data[$id][$this->year]->orderGoodCost += $total;
        $this->data[$id][$this->year]->orderSupplyCost += $order->getShippingCost();
    }

    /**
     * @inheritDoc
     */
    public function write(WriterInterface $writer): void
    {
        if ($writer instanceof XlsWriter) {
            $this->writeXls($writer);

            return;
        }

        throw new UnexpectedValueException('Unsupported writer');
    }

    private function writeXls(XlsWriter $writer): void
    {
        $sheet = $writer->createSheet('Supplier orders'); // TODO Trans

        $groups = [...$this->years];
        if (1 < count($this->years)) {
            $groups[] = 'Total';
        }

        $headerStyle = Xls::STYLE_BOLD + Xls::STYLE_BACKGROUND;

        // Headers
        $sheet->getColumnDimension('A')->setWidth(80, 'mm');
        $sheet->mergeCells([1, 1, 1, 3]);
        $sheet->getCell([1, 1])->getStyle()->applyFromArray($headerStyle);
        $sheet->getCell([1, 2])->getStyle()->applyFromArray($headerStyle);
        $sheet->getCell([1, 3])->getStyle()->applyFromArray($headerStyle + Xls::STYLE_BORDER_BOTTOM);
        $sheet->getCell([1, 1])->setValue('Fournisseur');

        foreach ($groups as $index => $header) {
            $col = 2 + $index * 5;

            // |--------------------------------------------------|
            // |                       YYYY                       |
            // |--------------------------------------------------|
            // |      Commandes clients      |    Fournisseurs    |
            // |-------------|------|--------|-------------|------|
            // | Marchandise | Port | Ventes | Marchandise | Port |
            // |-------------|------|--------|-------------|------|

            $sheet->mergeCells([$col, 1, $col + 4, 1]);
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray(
                $headerStyle + Xls::STYLE_CENTER + Xls::STYLE_BORDER_LEFT
            );
            $sheet->getCell([$col, 1])->setValue($header); // Year


            $sheet->mergeCells([$col, 2, $col + 2, 2]);
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray(
                $headerStyle + Xls::STYLE_CENTER + Xls::STYLE_BORDER_LEFT
            );
            $sheet->getCell([$col, 2])->setValue('Commandes clients');

            $sheet->getColumnDimensionByColumn($col)->setWidth(25, 'mm');
            $sheet->getCell([$col, 3])->getStyle()->applyFromArray($headerStyle + Xls::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col, 3])->getStyle()->applyFromArray(Xls::STYLE_BORDER_LEFT);
            $sheet->getCell([$col, 3])->setValue('Marchandise');

            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(18, 'mm');
            $sheet->getCell([$col + 1, 3])->getStyle()->applyFromArray($headerStyle + Xls::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col + 1, 3])->setValue('Port');

            $sheet->getColumnDimensionByColumn($col + 2)->setWidth(20, 'mm');
            $sheet->getCell([$col + 2, 3])->getStyle()->applyFromArray($headerStyle + Xls::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col + 2, 3])->setValue('Ventes');


            $sheet->mergeCells([$col + 3, 2, $col + 4, 2]);
            $sheet->getCell([$col + 3, 2])->getStyle()->applyFromArray(
                $headerStyle + Xls::STYLE_CENTER + Xls::STYLE_BORDER_LEFT
            );
            $sheet->getCell([$col + 3, 2])->setValue('Fournisseurs');

            $sheet->getColumnDimensionByColumn($col + 3)->setWidth(25, 'mm');
            $sheet->getCell([$col + 3, 3])->getStyle()->applyFromArray($headerStyle + Xls::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col + 3, 3])->getStyle()->applyFromArray(Xls::STYLE_BORDER_LEFT);
            $sheet->getCell([$col + 3, 3])->setValue('Marchandise');

            $sheet->getColumnDimensionByColumn($col + 4)->setWidth(18, 'mm');
            $sheet->getCell([$col + 4, 3])->getStyle()->applyFromArray($headerStyle + Xls::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col + 4, 3])->setValue('Port');
        }

        array_walk($this->data, function (array &$data) {
            $total = new SupplierData();
            foreach ($data as $datum) {
                $total->merge($datum);
            }
            $data['Total'] = $total;
        });

        uasort($this->data, function (array $a, array $b): int {
            return $b['Total']->orderGoodCost <=> $a['Total']->orderGoodCost;
        });

        $row = 3;
        foreach ($this->data as $id => $supplier) {
            $row++;

            $sheet->getCell([1, $row])->setValue($this->names[$id]);

            foreach ($groups as $index => $group) {
                $col = 2 + $index * 5;

                $data = $supplier[$group] ?? new SupplierData();

                $sheet->getCell([$col, $row])->getStyle()->applyFromArray(Xls::STYLE_BORDER_LEFT);

                $sheet->getCell([$col, $row])->setValue($data->saleGoodCost->toFixed(2));
                $sheet->getCell([$col + 1, $row])->setValue($data->saleSupplyCost->toFixed(2));
                $sheet->getCell([$col + 2, $row])->setValue($data->saleRevenue->toFixed(2));
                $sheet->getCell([$col + 3, $row])->setValue($data->orderGoodCost->toFixed(2));
                $sheet->getCell([$col + 4, $row])->setValue($data->orderSupplyCost->toFixed(2));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function requiresResources(): array
    {
        return [
            OrderInterface::class,
            SupplierOrderInterface::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function supportsWriter(string $writerClass): bool
    {
        return $writerClass === XlsWriter::class;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): TranslatableInterface
    {
        return t('supplier_order.label.plural', [], 'EkynaCommerce');
    }
}
