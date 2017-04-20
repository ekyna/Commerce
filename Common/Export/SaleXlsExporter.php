<?php

namespace Ekyna\Component\Commerce\Common\Export;

use Ekyna\Bundle\CommerceBundle\Service\Common\CommonRenderer;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Component\Intl\Currencies;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SaleXlsExporter
 * @package Ekyna\Component\Commerce\Common\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleXlsExporter implements SaleExporterInterface
{
    private const STYLE_TITLE = [
        'font' => [
            'size'  => 18,
            'color' => ['rgb' => '444444'],
        ],
    ];

    private const STYLE_ROW_HEADERS = [
        'font'    => [
            'bold' => true,
        ],
        'borders' => [
            'bottom' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    private const STYLE_QUANTITY = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    private const STYLE_CHILD_QUANTITY = [
        'font' => [
            'italic' => true,
            'color'  => ['rgb' => '999999'],
        ],
    ];

    private const STYLE_GRAND_TOTAL = [
        'font' => [
            'bold' => true,
        ],
    ];

    private const FORMAT_CURRENCY = [
        'EUR' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        'USD' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
    ];

    /**
     * @var View\ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var CommonRenderer
     */
    private $commonRenderer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Worksheet
     */
    private $sheet;

    /**
     * @var int
     */
    private $col = 0;

    /**
     * @var int
     */
    private $row = 0;


    /**
     * Constructor.
     *
     * @param View\ViewBuilder    $viewBuilder
     * @param CommonRenderer      $commonRenderer
     * @param TranslatorInterface $translator
     */
    public function __construct(
        View\ViewBuilder $viewBuilder,
        CommonRenderer $commonRenderer,
        TranslatorInterface $translator
    ) {
        $this->viewBuilder    = $viewBuilder;
        $this->commonRenderer = $commonRenderer;
        $this->translator     = $translator;
    }

    /**
     * @inheritDoc
     */
    public function export(SaleInterface $sale): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $this->sheet = $spreadsheet->getActiveSheet();
            $this->sheet->getColumnDimension('B')->setWidth(50);

            $view = $this->viewBuilder->buildSaleView($sale, [
                'private'  => false,
                'editable' => false,
                'export'   => true,
            ]);

            $this->buildHeader($sale);
            $this->buildRowHeaders($view);
            $start = $this->row + 1;
            $this->buildItemsRows($view, $view->getItems());
            $gtri = $this->buildGrossTotalsRow();
            $this->buildDiscountsRows($view, $gtri);
            $this->buildShipmentRow($view);
            $end = $this->row;
            $this->applyNumberFormats($view, $start, $end);
            $this->buildGranTotalsRows($view, $gtri);

            $writer = new Xls($spreadsheet);
            $path = sprintf('%s/%s.xls', sys_get_temp_dir(), $sale->getNumber());
            $writer->save($path);
        } catch (Exception $e) {
            throw new RuntimeException("Failed to generate XLS.");
        }

        return $path;
    }

    /**
     * Builds the header.
     *
     * @param SaleInterface $sale
     */
    private function buildHeader(SaleInterface $sale): void
    {
        $this->spacer();
        $this->row();

        if ($sale instanceof CartInterface) {
            $type = $this->translator->trans('cart.label.singular', [], 'EkynaCommerce');
        } elseif ($sale instanceof QuoteInterface) {
            $type = $this->translator->trans('quote.label.singular', [], 'EkynaCommerce');
        } else {
            $type = $this->translator->trans('order.label.singular', [], 'EkynaCommerce');
        }

        $this->sheet->mergeCells("B{$this->row}:K{$this->row}");
        $this->col = 1;
        $this->cell($type . ' ' . $sale->getNumber());
        $this->sheet->getStyle("B{$this->row}")->applyFromArray(self::STYLE_TITLE);

        $this->spacer();
        $this->row();

        $this->sheet->mergeCells("B{$this->row}:C{$this->row}");
        $this->col = 1;
        $this->cell($this->translator->trans('sale.field.invoice_address', [], 'EkynaCommerce'));
        $this->sheet->getStyle("B{$this->row}")->applyFromArray(self::STYLE_ROW_HEADERS);

        $this->sheet->mergeCells("D{$this->row}:E{$this->row}");

        $this->sheet->mergeCells("F{$this->row}:K{$this->row}");
        $this->col = 5;
        $this->cell($this->translator->trans('sale.field.delivery_address', [], 'EkynaCommerce'));
        $this->sheet->getStyle("F{$this->row}")->applyFromArray(self::STYLE_ROW_HEADERS);

        $this->row();

        $address = $this->commonRenderer->renderAddress($sale->getInvoiceAddress());
        $address = strip_tags(str_replace('<br>', "\n", $address));
        $address = preg_replace("~\n+~", "\n", $address);

        $this->sheet->mergeCells("B{$this->row}:C{$this->row}");
        $this->col = 1;
        $this->cell($address);

        if (!$sale->isSameAddress()) {
            $address = $this->commonRenderer->renderAddress($sale->getDeliveryAddress());
            $address = strip_tags(str_replace('<br>', "\n", $address));
            $address = preg_replace("~\n+~", "\n", $address);
        }

        $this->sheet->mergeCells("D{$this->row}:E{$this->row}");
        $this->sheet->mergeCells("F{$this->row}:K{$this->row}");
        $this->col = 5;
        $this->cell($address);

        $this->spacer();
    }

    /**
     * Build headers.
     *
     * @param View\SaleView $view
     */
    private function buildRowHeaders(View\SaleView $view): void
    {
        $this->spacer();

        $trans = $view->getTranslations();

        $this->row();

        // A - Number
        $this->cell('');
        // B - Designation
        $this->cell($trans['designation']);
        // C - Reference
        $this->cell($trans['reference']);
        // D - Unit price
        $this->cell($trans['unit_net_price']);
        // E - Quantity
        $this->cell($trans['quantity']);
        // F - Gross
        $this->cell($trans['net_gross']);
        // G - Discount rate
        // H - Discount amount
        $this->sheet->mergeCells("G{$this->row}:H{$this->row}");
        $this->cell($this->translator->trans('sale.field.discount', [], 'EkynaCommerce'));
        // I - Total
        $this->col = 8;
        $this->cell($trans['net_total']);
        // J - Tax rate
        // K - Tax amount
        $this->sheet->mergeCells("J{$this->row}:K{$this->row}");
        $this->cell($this->translator->trans('field.vat', [], 'EkynaCommerce'));
        $this->col = 11;
        // L - Ati Total
        //$this->cell($trans['ati_total']);

        $this->sheet->getStyle("B{$this->row}:K{$this->row}")->applyFromArray(self::STYLE_ROW_HEADERS);
    }

    /**
     * Builds the items rows.
     *
     * @param View\SaleView   $view
     * @param View\LineView[] $lines
     */
    private function buildItemsRows(View\SaleView $view, array $lines): void
    {
        $this->spacer();

        foreach ($lines as $line) {
            $this->buildItemRow($view, $line);
        }
    }

    /**
     * Applies the number formats.
     *
     * @param View\SaleView $view
     * @param int           $from
     * @param int           $to
     */
    private function applyNumberFormats(View\SaleView $view, int $from, int $to): void
    {
        $currency = $this->getCurrencyFormat($view->getCurrency());

        $this->sheet
            ->getStyle("D{$from}:D{$to}")
            ->getNumberFormat()
            ->setFormatCode($currency);

        $this->sheet
            ->getStyle("F{$from}:F{$to}")
            ->getNumberFormat()
            ->setFormatCode($currency);

        $this->sheet
            ->getStyle("G{$from}:G{$to}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

        $this->sheet
            ->getStyle("H{$from}:I{$to}")
            ->getNumberFormat()
            ->setFormatCode($currency);

        $this->sheet
            ->getStyle("J{$from}:J{$to}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

        $this->sheet
            ->getStyle("K{$from}:K{$to}")
            ->getNumberFormat()
            ->setFormatCode($currency);
    }

    /**
     * Build the item row.
     *
     * @param View\SaleView $view
     * @param View\LineView $line
     * @param int           $parentRow
     */
    private function buildItemRow(View\SaleView $view, View\LineView $line, int $parentRow = null): void
    {
        if ($line->isPrivate()) {
            return;
        }

        $this->row();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $line->getSource();

        // A - Number
        $this->cell($line->getNumber());
        // B - Designation
        $this->cell(implode('', array_fill(0, $line->getLevel(), ' - ')) . $line->getDesignation());
        if (isset($line->vars['link'])) {
            $this->getCell()->getHyperlink()->setUrl($line->vars['link']['href']);
        }
        // C - Reference
        $this->cell($item->getReference());
        // D - Unit price
        $this->cell($line->getUnit());
        // E - Quantity
        if (is_null($parentRow)) {
            $this->cell($line->getQuantity());
            $this->sheet->getStyle("E{$this->row}")->applyFromArray(self::STYLE_QUANTITY);
        } else {
            $this->cell(sprintf("=%f*E%s", $item->getQuantity(), $parentRow));
            $this->sheet->getStyle("E{$this->row}")->applyFromArray(self::STYLE_CHILD_QUANTITY);
        }

        $unit = Units::PIECE;
        if ($item->hasSubjectIdentity()) {
            $subject = $item->getSubjectIdentity()->getSubject();
            if ($subject instanceof StockSubjectInterface) {
                $unit = $subject->getUnit();
            }
        }

        $this->sheet
            ->getStyle("E{$this->row}")
            ->getNumberFormat()
            ->setFormatCode(Units::PIECE === $unit ? NumberFormat::FORMAT_NUMBER : NumberFormat::FORMAT_NUMBER_00);

        // F - Gross
        $this->cell("=D{$this->row}*E{$this->row}");
        // G - Discount rate
        $this->cell($line->getDiscountRates());
        // H - Discount amount
        $this->cell("=-F{$this->row}*G{$this->row}");
        // I - Net total
        $this->cell("=F{$this->row}+H{$this->row}");
        // J - Tax rate
        $this->cell($line->getTaxRates());
        // K - Tax amount
        $this->cell("=I{$this->row}*J{$this->row}");
        // I - Ati total
        //$this->cell("=I{$this->row}+K{$this->row}");

        $row = $this->row;
        foreach ($line->getLines() as $child) {
            $this->buildItemRow($view, $child, $row);
        }
    }

    /**
     * Builds the gross totals row.
     *
     * @return int The gross totals row index
     */
    private function buildGrossTotalsRow(): int
    {
        $this->spacer();

        $this->row();

        // A - Number
        // E - Quantity
        $this->sheet->mergeCells("B{$this->row}:E{$this->row}");
        $this->col = 1;
        $this->cell($this->translator->trans('sale.field.gross_totals', [], 'EkynaCommerce'));
        $this->col = 5;

        // F - Gross
        $this->cell(sprintf("=SUM(F2:F%d)", $this->row - 2));
        // G - Discount rate
        $this->cell('');
        // H - Discount amount
        $this->cell(sprintf("=SUM(H2:H%d)", $this->row - 2));
        // I - Net total
        $this->cell(sprintf("=SUM(I2:I%d)", $this->row - 2));
        // J - Tax rate
        $this->cell('');
        // K - Tax amount
        $this->cell(sprintf("=SUM(K2:K%d)", $this->row - 2));
        // I - Ati total
        //$this->cell(sprintf("=SUM(L2:L%d)", $this->row - 2));

        return $this->row;
    }

    /**
     * Builds the discounts rows.
     *
     * @param View\SaleView $view
     * @param int           $gtri The gross total row index
     */
    private function buildDiscountsRows(View\SaleView $view, int $gtri): void
    {
        if (empty($discounts = $view->getDiscounts())) {
            return;
        }

        $this->spacer();

        foreach ($discounts as $discount) {
            $this->buildDiscountRow($discount, $gtri);
        }
    }

    /**
     * Builds the discount row.
     *
     * @param View\LineView $line
     * @param int           $gtri The gross total row index
     */
    private function buildDiscountRow(View\LineView $line, int $gtri): void
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
        $adjustment = $line->getSource();

        // TODO Temporary skip non percent discount
        if ($adjustment->getMode() !== AdjustmentModes::MODE_PERCENT) {
            return;
        }

        $this->row();

        // A - Number
        $this->cell('');
        // B - Description
        $this->cell($line->getDesignation());

        if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
            // F - Gross
            $this->sheet->mergeCells("C{$this->row}:F{$this->row}");
            $this->col = 6;
            // G - Discount rate
            $this->cell($adjustment->getAmount());
            // H - Discount amount
            $this->cell('');
            // I - Net total
            if ($this->row === $gtri + 2) {
                $this->cell(sprintf("=-I%d*G%s/100", $gtri, $this->row));
            } else {
                $this->cell(sprintf("=-(I%d+SUM(I%d:I%d))*G%d/100", $gtri, $gtri + 2, $this->row, $this->row));
            }
            // J - Tax rate
            $this->cell('');
            // K - Tax amount
            if ($this->row === $gtri + 2) {
                $this->cell(sprintf("=-K%d*G%s/100", $gtri, $this->row));
            } else {
                $this->cell(sprintf("=-(K%d+SUM(K%d:K%d))*G%d/100", $gtri, $gtri + 2, $this->row, $this->row));
            }
        } else {
            // H - Discount amount
            $this->sheet->mergeCells("C{$this->row}:H{$this->row}");
            $this->col = 8;
            // I - Net total
            $this->cell($adjustment->getAmount());
            // J - Tax rate
            $this->cell('');
            // K - Tax amount
            if ($this->row === $gtri + 2) {
                $this->cell(0); // TODO
            } else {
                $this->cell(0); // TODO
            }
        }
    }

    /**
     * Builds the shipment row.
     *
     * @param View\SaleView $view
     */
    private function buildShipmentRow(View\SaleView $view): void
    {
        $this->spacer();
        $this->row();

        $line = $view->getShipment();

        // A - Number
        $this->cell('');
        // B - Description
        $this->cell($line ? $line->getDesignation() : '');
        // H - Discount amount
        $this->sheet->mergeCells("C{$this->row}:H{$this->row}");
        $this->col = 8;
        // I - Net total
        $this->cell($line ? (string)(float)$line->getBase() : 0); // TODO (string)(float) because it may be 'Offert'
        // J - Tax rate
        $this->cell($line ? $line->getTaxRates() : 0);
        // K - Tax amount
        $this->cell(sprintf("=I{$this->row}*J{$this->row}"));
    }

    /**
     * Builds the grand total rows.
     *
     * @param View\SaleView $view
     * @param int           $gtri The gross total row index
     */
    private function buildGranTotalsRows(View\SaleView $view, int $gtri): void
    {
        $trans = $view->getTranslations();

        $this->spacer();

        $this->buildGranTotalRow($trans['net_total']);
        $this->cell(sprintf('=SUM(I%d:I%d)', $gtri, $this->row - 2));

        $this->buildGranTotalRow($trans['tax_total']);
        $this->cell(sprintf('=SUM(K%d:K%d)', $gtri, $this->row - 2));

        $this->buildGranTotalRow($trans['ati_total']);
        $this->cell(sprintf('=SUM(I%d:I%d)', $this->row - 2, $this->row - 1));

        $this->sheet
            ->getStyle(sprintf('I%d:I%d', $this->row - 2, $this->row))
            ->getNumberFormat()
            ->setFormatCode($this->getCurrencyFormat($view->getCurrency()));

        // Styles
        $this->sheet->getStyle(sprintf('G%d:G%d', $this->row - 2, $this->row))->applyFromArray(self::STYLE_GRAND_TOTAL);
        $this->sheet->getStyle(sprintf('I%d', $this->row))->applyFromArray(self::STYLE_GRAND_TOTAL);
    }

    /**
     * Prepare a gran total row.
     *
     * @param string $label
     */
    private function buildGranTotalRow(string $label): void
    {
        $this->row();
        // F - Gross
        $this->sheet->mergeCells("A{$this->row}:F{$this->row}");
        $this->col = 6;
        // G - Discount rate
        $this->sheet->mergeCells("G{$this->row}:H{$this->row}");
        $this->cell($label);
        $this->col = 8;
    }

    /**
     * Increment row.
     */
    private function row(): void
    {
        $this->row++;
        $this->col = 0;
    }

    /**
     * Returns the next cell number.
     *
     * @return string
     */
    private function col(): string
    {
        return ++$this->col;
    }

    /**
     * Writes value into the next cell.
     *
     * @param string $value
     */
    private function cell(string $value = null): void
    {
        $this->sheet->setCellValueByColumnAndRow($this->col(), $this->row, $value);
    }

    /**
     * Returns the latest written cell.
     *
     * @return Cell|null
     */
    private function getCell(): ?Cell
    {
        return $this->sheet->getCellByColumnAndRow($this->col, $this->row);
    }

    /**
     * Adds a spacer blank line with merged cells.
     */
    private function spacer(): void
    {
        $this->row();
        $this->sheet->mergeCellsByColumnAndRow(1, $this->row, 12, $this->row);
    }

    /**
     * Returns the number format for the given currency.
     *
     * @param string $currency
     *
     * @return string
     */
    private function getCurrencyFormat(string $currency): string
    {
        if (isset(self::FORMAT_CURRENCY[$currency])) {
            return self::FORMAT_CURRENCY[$currency];
        }

        $symbol = Currencies::getSymbol($currency);

        return str_replace('$', $symbol, NumberFormat::FORMAT_CURRENCY_USD);
    }
}
