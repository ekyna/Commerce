<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Writer;

use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Resource\Helper\File\Xls as Style;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;
use function sys_get_temp_dir;
use function tempnam;

/**
 * Class XlsWriter
 * @package Ekyna\Component\Commerce\Report\Writer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class XlsWriter implements WriterInterface
{
    final public const NAME = 'xls';

    private Spreadsheet $spreadsheet;
    private int         $sheetIndex;
    private int         $columnBase;

    public function createSheet(string $title): Worksheet
    {
        if (0 === $this->sheetIndex) {
            $sheet = $this->spreadsheet->getActiveSheet();
        } else {
            $sheet = $this->spreadsheet->createSheet();

            $this->spreadsheet->setActiveSheetIndex($this->sheetIndex);
        }

        $this->sheetIndex++;

        $sheet->setTitle($title);

        return $sheet;
    }

    public function writeMarginHeaders(array $headers, array $years): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Columns headers
        $headerStyle = Style::STYLE_BOLD + Style::STYLE_BACKGROUND;

        $col = 1;
        foreach ($headers as $header => $width) {
            $sheet->getColumnDimensionByColumn($col)->setWidth($width, 'mm');

            $sheet->mergeCells([$col, 1, $col, 3]);
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col, 3])->getStyle()->applyFromArray($headerStyle + Style::STYLE_BORDER_BOTTOM);
            $sheet->getCell([$col, 1])->setValue($header);
            $col++;
        }

        $yearStyle =
            Style::STYLE_BOLD
            + Style::STYLE_CENTER
            + Style::STYLE_BACKGROUND
            + Style::STYLE_BORDER_LEFT;

        $this->columnBase = $col;
        foreach ($years as $index => $year) {
            $col = $this->columnBase + $index * 9;

            // Year
            $sheet->mergeCells([$col, 1, $col + 8, 1]);
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col, 1])->setValue($year);

            // Revenue
            $sheet->mergeCells([$col, 2, $col + 1, 2]);
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col, 2])->setValue('Revenue'); // TODO Trans
            // Cost
            $sheet->mergeCells([$col + 2, 2, $col + 4, 2]);
            $sheet->getCell([$col + 2, 2])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col + 2, 2])->setValue('Cost'); // TODO Trans
            // Net Margin
            $sheet->mergeCells([$col + 5, 2, $col + 6, 2]);
            $sheet->getCell([$col + 5, 2])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col + 5, 2])->setValue('Gross margin'); // TODO Trans
            // Gross Margin
            $sheet->mergeCells([$col + 7, 2, $col + 8, 2]);
            $sheet->getCell([$col + 7, 2])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col + 7, 2])->setValue('Net margin'); // TODO Trans

            // Revenue product
            $sheet->getColumnDimensionByColumn($col)->setWidth(20, 'mm');
            $sheet->getCell([$col, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col, 3])->getStyle()->applyFromArray(Style::STYLE_BORDER_LEFT);
            $sheet->getCell([$col, 3])->setValue('Product'); // TODO Trans
            // Revenue shipping
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 1, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 1, 3])->setValue('Shipment'); // TODO Trans
            // Cost product
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 2, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 2, 3])->setValue('Product'); // TODO Trans
            // Cost supply
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 3, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 3, 3])->setValue('Supply'); // TODO Trans
            // Cost shipment
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 4, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 4, 3])->setValue('Shipment'); // TODO Trans
            // Gross margin amount
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 5, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 5, 3])->setValue('Amount'); // TODO Trans
            // Gross margin percent
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 6, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 6, 3])->setValue('Percent'); // TODO Trans
            // Net margin amount
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 7, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 7, 3])->setValue('Amount'); // TODO Trans
            // Net margin percent
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(20, 'mm');
            $sheet->getCell([$col + 8, 3])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 8, 3])->setValue('Percent'); // TODO Trans
        }
    }

    public function writeMarginCells(Margin $data, int $yearIndex, int $row): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $col = $this->columnBase + $yearIndex * 9;

        // Left border
        $sheet->getCell([$col, $row])->getStyle()->applyFromArray(Style::STYLE_BORDER_LEFT);

        // Cells values
        $sheet->getCell([$col, $row])->setValue($data->getRevenueProduct()->toFixed(2));
        $sheet->getCell([$col + 1, $row])->setValue($data->getRevenueShipment()->toFixed(2));
        $sheet->getCell([$col + 2, $row])->setValue($data->getCostProduct()->toFixed(2));
        $sheet->getCell([$col + 3, $row])->setValue($data->getCostSupply()->toFixed(2));
        $sheet->getCell([$col + 4, $row])->setValue($data->getCostShipment()->toFixed(2));
        $sheet->getCell([$col + 5, $row])->setValue($data->getTotal(true)->toFixed(2));
        $sheet->getCell([$col + 6, $row])->setValue($data->getPercent(true)->toFixed(1));
        $sheet->getCell([$col + 7, $row])->setValue($data->getTotal(false)->toFixed(2));
        $sheet->getCell([$col + 8, $row])->setValue($data->getPercent(false)->toFixed(1));
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheetIndex = 0;
    }

    /**
     * @inheritDoc
     */
    public function terminate(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'report');

        $writer = new Xls($this->spreadsheet);
        $writer->save($path);

        return $path;
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
        return t('report.writer.xls', [], 'EkynaCommerce');
    }
}
