<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Writer;

use Ekyna\Component\Commerce\Report\Section\Model\OrderData;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

    private const HEADER_BACKGROUND = 'FFE3E3E3';

    public const STYLE_BOLD = [
        'font'      => [
            'bold' => true,
        ],
    ];

    public const STYLE_CENTER = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
    ];

    public const STYLE_BACKGROUND = [
        'fill'      => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => self::HEADER_BACKGROUND,
            ],
        ],
    ];

    public const STYLE_BORDER_BOTTOM = [
        'borders' => [
            'bottom' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    public const STYLE_BORDER_RIGHT = [
        'borders' => [
            'right' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    public const STYLE_BORDER_LEFT = [
        'borders' => [
            'left' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    private readonly Spreadsheet $spreadsheet;
    private int                  $index;

    public function createSheet(string $title): Worksheet
    {
        if (0 === $this->index) {
            $sheet = $this->spreadsheet->getActiveSheet();
        } else {
            $sheet = $this->spreadsheet->createSheet();

            $this->spreadsheet->setActiveSheetIndex($this->index);
        }

        $this->index++;

        $sheet->setTitle($title);

        return $sheet;
    }

    public function writeOrderStatHeaders(string $header, int $width, array $years): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Columns headers
        $sheet->getColumnDimension('A')->setWidth($width, 'mm');

        $headerStyle = XlsWriter::STYLE_BOLD + XlsWriter::STYLE_BACKGROUND;

        $sheet->mergeCells([1, 1, 1, 2]);
        $sheet->getCell([1, 1])->getStyle()->applyFromArray($headerStyle);
        $headerStyle += XlsWriter::STYLE_BORDER_BOTTOM;
        $sheet->getCell([1, 2])->getStyle()->applyFromArray($headerStyle);
        $sheet->getCell([1, 1])->setValue($header);

        $yearStyle =
            XlsWriter::STYLE_BOLD
            + XlsWriter::STYLE_CENTER
            + XlsWriter::STYLE_BACKGROUND
            + XlsWriter::STYLE_BORDER_LEFT;

        foreach ($years as $index => $year) {
            $col = 2 + $index * 3;

            // Year (merged cells)
            $sheet->mergeCells([$col, 1, $col + 2, 1]);
            $sheet->getCell([$col, 1])->getStyle()->applyFromArray($yearStyle);
            $sheet->getCell([$col, 1])->setValue($year);

            // Revenue
            $sheet->getColumnDimensionByColumn($col)->setWidth(20, 'mm');
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col, 2])->getStyle()->applyFromArray(XlsWriter::STYLE_BORDER_LEFT);
            $sheet->getCell([$col, 2])->setValue('CA'); // TODO Trans

            // Gross margin
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(22, 'mm');
            $sheet->getCell([$col + 1, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 1, 2])->setValue('Marge Brut.'); // TODO Trans

            // Commercial margin
            $sheet->getColumnDimensionByColumn($col + 2)->setWidth(25, 'mm');
            $sheet->getCell([$col + 2, 2])->getStyle()->applyFromArray($headerStyle);
            $sheet->getCell([$col + 2, 2])->setValue('Marge Comm.'); // TODO Trans
        }
    }

    public function writeOrderStatCells(OrderData $data, int $col, int $row): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Left border
        $sheet->getCell([$col, $row])->getStyle()->applyFromArray(XlsWriter::STYLE_BORDER_LEFT);

        // Cells values
        $sheet->getCell([$col, $row])->setValue($data->grossMargin->getRevenue());
        $sheet->getCell([$col + 1, $row])->setValue($data->grossMargin->getPercent());
        $sheet->getCell([$col + 2, $row])->setValue($data->commercialMargin->getPercent());
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->index = 0;
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
