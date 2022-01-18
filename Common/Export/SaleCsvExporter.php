<?php

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
    /**
     * @var View\ViewBuilder
     */
    private $viewBuilder;


    /**
     * Constructor.
     *
     * @param View\ViewBuilder $viewBuilder
     */
    public function __construct(View\ViewBuilder $viewBuilder)
    {
        $this->viewBuilder = $viewBuilder;
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
            $this->buildDiscountsRows($view, $view->getDiscounts(), $rows);
        }

        if ($shipment = $view->getShipment()) {
            $rows[] = []; // TODO Spacer
            $this->buildShipmentRow($view, $shipment, $rows);
        }

        $rows[] = []; // TODO Spacer

        $this->buildGranTotalsRows($view, $rows);

        return $this->buildCsv($sale->getNumber() . '_csv_export', $rows);
    }

    /**
     * Builds the header row.
     *
     * @param View\SaleView $view
     *
     * @return array
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

        $row[] = $view->isAti() ? $trans['unit_ati_price'] : $trans['unit_net_price'];

        if ($view->vars['show_taxes']) {
            $row[] = $trans['tax_rate'];
        }

        $row[] = $trans['quantity'];

        if ($view->vars['show_discounts']) {
            $row[] = $view->isAti() ? $trans['ati_gross'] : $trans['net_gross'];
            $row[] = $trans['discount']; // TODO Percent
            $row[] = ''; // TODO Amount
        }

        $row[] = $view->isAti() ? $trans['ati_total'] : $trans['net_total'];

        if ($view->vars['show_margin']) {
            $row[] = $trans['margin'];
        }

        return $row;
    }

    /**
     * Builds the items rows.
     *
     * @param View\SaleView   $view
     * @param View\LineView[] $lines
     * @param array           $rows
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
     * @param View\SaleView $view
     * @param View\LineView $line
     * @param array         $rows
     */
    private function buildItemRow(View\SaleView $view, View\LineView $line, array &$rows): void
    {
        if ($line->isPrivate()) {
            return;
        }

        $row = [
            $line->getNumber(),
            implode('', array_fill(0, $line->getLevel(), ' - ')) . $line->getDesignation(),
            // TODO prefix with tree symbols
            // TODO Description (?)
            $line->getReference(),
        ];

        if ($view->vars['show_availability']) {
            $row[] = strip_tags($line->getAvailability() ?? '');
        }

        $row[] = $line->getUnit();

        if ($view->vars['show_taxes']) {
            $row[] = $line->getTaxRates();
        }

        $row[] = $line->getQuantity();

        if ($view->vars['show_discounts']) {
            $row[] = $line->getGross();
            $row[] = $line->getDiscountRates();
            $row[] = $line->getDiscountAmount() ? '-' . $line->getDiscountAmount() : '';
        }

        $row[] = $line->getBase();

        if ($view->vars['show_margin']) {
            $row[] = $line->getMargin();
        }

        $rows[] = $row;

        foreach ($line->getLines() as $child) {
            $this->buildItemRow($view, $child, $rows);
        }
    }

    /**
     * Builds the gross totals row.
     *
     * @param View\SaleView $view
     * @param array         $rows
     */
    private function buildGrossTotalsRow(View\SaleView $view, array &$rows): void
    {
        $offset = $view->vars['columns_count']
            - ($view->vars['show_discounts'] ? 4 : 1)
            + ($view->vars['show_availability'] ? 1 : 0);

        $row = array_fill(0, $offset, null);

        if ($view->vars['show_discounts']) {
            $row[] = $view->getGross()->getBase();
            $row[] = $view->getGross()->getAdjustment();
        }

        $row[] = $view->getGross()->getTotal();

        $rows[] = $row;
    }

    /**
     * Builds the discounts rows.
     *
     * @param View\SaleView $view
     * @param array         $discounts
     * @param array         $rows
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
     * @param View\SaleView $view
     * @param View\LineView $line
     * @param array         $rows
     */
    private function buildDiscountRow(View\SaleView $view, View\LineView $line, array &$rows): void
    {
        $row = [
            null,
            $line->getDesignation(),
            // TODO Description (?)
        ];

        $row += array_fill(sizeof($row), $view->vars['show_availability'] ? 3 : 2, null);

        if ($view->vars['show_taxes']) {
            $row[] = $line->getTaxRates();
        }

        $row += array_fill(sizeof($row), $view->vars['show_discounts'] ? 4 : 1, null);

        $row[] = '-' . ($view->isAti() ? $line->getTotal() : $line->getBase());

        $rows[] = $row;
    }

    /**
     * Builds the shipment row.
     *
     * @param View\SaleView $view
     * @param View\LineView $line
     * @param array         $rows
     */
    private function buildShipmentRow(View\SaleView $view, View\LineView $line, array &$rows): void
    {
        $row = [
            null,
            $line->getDesignation(),
            // TODO Description (?)
        ];

        $row += array_fill(sizeof($row), $view->vars['show_availability'] ? 3 : 2, null);

        if ($view->vars['show_taxes']) {
            $row[] = $line->getTaxRates();
        }

        $row += array_fill(sizeof($row), $view->vars['show_discounts'] ? 4 : 1, null);

        $row[] = ($view->isAti() ? $line->getTotal() : $line->getBase());

        $rows[] = $row;
    }

    /**
     * Builds the grand total rows.
     *
     * @param View\SaleView $view
     * @param array         $rows
     */
    private function buildGranTotalsRows(View\SaleView $view, array &$rows): void
    {
        $trans = $view->getTranslations();

        $base = array_fill(0, $view->vars['columns_count'] - ($view->vars['show_margin'] ? 3 : 2), null);

        $row = $base;
        $row[] = $view->isAti() ? $trans['ati_total'] : $trans['net_total'];
        $row[] = $view->isAti() ? $view->getFinal()->getTotal() : $view->getFinal()->getBase();
        $rows[] = $row;

        $row = $base;
        $row[] = $trans['tax_total'];
        $row[] = $view->getFinal()->getAdjustment();
        $rows[] = $row;

        if (!$view->isAti()) {
            $row = $base;
            $row[] = $trans['ati_total'];
            $row[] = $view->getFinal()->getTotal();
            $rows[] = $row;
        }
    }

    /**
     * Builds the CSV file.
     *
     * @param string $name
     * @param array  $rows
     *
     * @return string
     */
    private function buildCsv(string $name, array $rows): string
    {
        if (false === $path = tempnam(sys_get_temp_dir(), $name)) {
            throw new RuntimeException("Failed to create temporary file.");
        }

        if (false === $handle = fopen($path, "w")) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}
