<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Export;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class SaleCsvExporter
 * @package Ekyna\Component\Commerce\Order\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCsvExporter implements SaleExporterInterface
{
    public function __construct(
        private readonly View\ViewBuilder $viewBuilder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function export(SaleInterface $sale): string
    {
        $view = $this->viewBuilder->buildSaleView($sale, [
            'private'  => false,
            'editable' => false,
            'export'   => true,
        ]);

        $rows = [$this->buildHeaderRow($view)];

        $this->buildItemsRows($view, $view->getItems(), $rows);

        if (!empty($discounts = $view->getDiscounts())) {
            $this->buildGrossTotalsRow($view, $rows);
            $rows[] = []; // TODO Spacer
            $this->buildDiscountsRows($view, $discounts, $rows);
        }

        if (null !== $view->shipment) {
            $rows[] = []; // TODO Spacer
            $this->buildShipmentRow($view, $view->shipment, $rows);
        }

        $rows[] = []; // TODO Spacer

        $this->buildGranTotalsRows($view, $rows);

        return $this->buildCsv($sale->getNumber() . '_csv_export', $rows);
    }

    /**
     * Builds the header row.
     */
    private function buildHeaderRow(View\SaleView $view): array
    {
        $trans = $view->getTranslations();

        $row = [
            '',
            $trans['designation'],
            $trans['reference'],
        ];

        if ($view->vars['show_availability']) {
            $row[] = $trans['availability'];
        }

        $row[] = $view->ati ? $trans['unit_ati_price'] : $trans['unit_net_price'];

        if ($view->vars['show_taxes']) {
            $row[] = $trans['tax_rate'];
        }

        $row[] = $trans['quantity'];

        if ($view->vars['show_discounts']) {
            $row[] = $view->ati ? $trans['ati_gross'] : $trans['net_gross'];
            $row[] = $trans['discount']; // TODO Percent
            $row[] = '';                 // TODO Amount
        }

        $row[] = $view->ati ? $trans['ati_total'] : $trans['net_total'];

        if ($view->vars['show_margin']) {
            $row[] = $trans['margin'];
        }

        return $row;
    }

    /**
     * Builds the items rows.
     *
     * @param array<View\LineView> $lines
     * @param array<array>         $rows
     */
    private function buildItemsRows(View\SaleView $view, array $lines, array &$rows): void
    {
        foreach ($lines as $line) {
            $this->buildItemRow($view, $line, $rows);
        }
    }

    /**
     * Build the item row.
     *
     * @param array<array> $rows
     */
    private function buildItemRow(View\SaleView $view, View\LineView $line, array &$rows): void
    {
        if ($line->private) {
            return;
        }

        $row = [
            $line->number,
            implode('', array_fill(0, $line->level, ' - ')) . $line->designation,
            // TODO prefix with tree symbols
            // TODO Description (?)
            $line->reference,
        ];

        if ($view->vars['show_availability']) {
            $row[] = strip_tags($line->availability ?? '');
        }

        $row[] = $line->unit;

        if ($view->vars['show_taxes']) {
            $row[] = $line->taxRates;
        }

        $row[] = $line->quantity;

        if ($view->vars['show_discounts']) {
            $row[] = $line->gross;
            $row[] = $line->discountRates;
            $row[] = $line->discountAmount ? '-' . $line->discountAmount : '';
        }

        $row[] = $line->base;

        if ($view->vars['show_margin']) {
            $row[] = $line->margin;
        }

        $rows[] = $row;

        foreach ($line->getLines() as $child) {
            $this->buildItemRow($view, $child, $rows);
        }
    }

    /**
     * Builds the gross totals row.
     *
     * @param array<array> $rows
     */
    private function buildGrossTotalsRow(View\SaleView $view, array &$rows): void
    {
        $offset = $view->vars['columns_count']
            - ($view->vars['show_discounts'] ? 4 : 1)
            + ($view->vars['show_availability'] ? 1 : 0);

        $row = array_fill(0, $offset, null);

        if ($view->vars['show_discounts']) {
            $row[] = $view->gross->base;
            $row[] = $view->gross->adjustment;
        }

        $row[] = $view->gross->total;

        $rows[] = $row;
    }

    /**
     * Builds the discounts rows.
     *
     * @param array<array> $rows
     */
    private function buildDiscountsRows(View\SaleView $view, array $discounts, array &$rows): void
    {
        foreach ($discounts as $discount) {
            $this->buildDiscountRow($view, $discount, $rows);
        }
    }

    /**
     * Builds the discount row.
     *
     * @param array<array> $rows
     */
    private function buildDiscountRow(View\SaleView $view, View\LineView $line, array &$rows): void
    {
        $row = [
            null,
            $line->designation,
            // TODO Description (?)
        ];

        $row += array_fill(sizeof($row), $view->vars['show_availability'] ? 3 : 2, null);

        if ($view->vars['show_taxes']) {
            $row[] = $line->taxRates;
        }

        $row += array_fill(sizeof($row), $view->vars['show_discounts'] ? 4 : 1, null);

        $row[] = '-' . ($view->ati ? $line->total : $line->base);

        $rows[] = $row;
    }

    /**
     * Builds the shipment row.
     *
     * @param array<array> $rows
     */
    private function buildShipmentRow(View\SaleView $view, View\LineView $line, array &$rows): void
    {
        $row = [
            null,
            $line->designation,
            // TODO Description (?)
        ];

        $row += array_fill(sizeof($row), $view->vars['show_availability'] ? 3 : 2, null);

        if ($view->vars['show_taxes']) {
            $row[] = $line->taxRates;
        }

        $row += array_fill(sizeof($row), $view->vars['show_discounts'] ? 4 : 1, null);

        $row[] = $view->ati ? $line->total : $line->base;

        $rows[] = $row;
    }

    /**
     * Builds the grand total rows.
     *
     * @param array<array> $rows
     */
    private function buildGranTotalsRows(View\SaleView $view, array &$rows): void
    {
        $trans = $view->getTranslations();

        $base = array_fill(0, $view->vars['columns_count'] - ($view->vars['show_margin'] ? 3 : 2), null);

        $row = $base;
        $row[] = $view->ati ? $trans['ati_total'] : $trans['net_total'];
        $row[] = $view->ati ? $view->final->total : $view->final->base;
        $rows[] = $row;

        $row = $base;
        $row[] = $trans['tax_total'];
        $row[] = $view->final->adjustment;
        $rows[] = $row;

        if (!$view->ati) {
            $row = $base;
            $row[] = $trans['ati_total'];
            $row[] = $view->final->total;
            $rows[] = $row;
        }
    }

    /**
     * Builds the CSV file.
     */
    private function buildCsv(string $name, array $rows): string
    {
        if (false === $path = tempnam(sys_get_temp_dir(), $name)) {
            throw new RuntimeException('Failed to create temporary file.');
        }

        if (false === $handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}
