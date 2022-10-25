<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section;

use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Section\Model\InvoiceData;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function explode;
use function Symfony\Component\Translation\t;

/**
 * Class InvoicesSection
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoicesSection implements SectionInterface
{
    final public const NAME = 'invoices';

    private string $locale;
    /** @var array<string, array<int, InvoiceData>> */
    private array $data;
    /** @var array<int, string> */
    private array $years;

    /**
     * @inheritDoc
     */
    public function initialize(ReportConfig $config): void
    {
        $this->locale = $config->locale;
        $this->data = [];
        $this->years = $config->range->getYears();
    }

    /**
     * @inheritDoc
     */
    public function read(ResourceInterface $resource): void
    {
        if (!$resource instanceof OrderInvoiceInterface) {
            throw new UnexpectedTypeException($resource, OrderInvoiceInterface::class);
        }

        [$year, $month] = explode('-', $resource->getCreatedAt()->format('Y-n'));

        if (!isset($this->data[$year][$month])) {
            $this->data[$year][$month] = new InvoiceData();
        }

        $data = $this->data[$year][$month];

        $data->good += $resource->getGoodsBase();
        $data->shipment += $resource->getShipmentBase();
        $data->discount += $resource->getDiscountBase();
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
        $sheet = $writer->createSheet('Invoices');

        // Column headers
        $headerStyle = XlsWriter::STYLE_BOLD + XlsWriter::STYLE_BACKGROUND + XlsWriter::STYLE_BORDER_BOTTOM;

        $sheet->getColumnDimension('A')->setWidth(30, 'mm');
        $sheet->getCell([1, 1])->getStyle()->applyFromArray($headerStyle);
        $sheet->getCell([1, 1])->setValue('Month');

        foreach ($this->years as $index => $year) {
            $col = 2 + $index;
            $sheet->getColumnDimensionByColumn($col)->setWidth(25, 'mm');
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col, 1])->setValue($year);
        }

        // Rows
        foreach (DateUtil::getMonths($this->locale) as $monthIndex => $month) {
            $row = 1 + $monthIndex; // monthIndex is 1 based

            // Row header
            $sheet->getCell([1, $row])->setValue($month);

            // Values
            foreach ($this->years as $yearIndex => $year) {
                $col = 2 + $yearIndex; // yearIndex is 0 based

                $data = $this->data[$year][$monthIndex] ?? new InvoiceData();

                $value = $data->good->add($data->shipment)->sub($data->discount);

                $sheet->getCell([$col, $row])->setValue($value->toFixed());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function requiresResources(): array
    {
        return [OrderInvoiceInterface::class];
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
        return t('invoice.label.plural', [], 'EkynaCommerce');
    }
}
