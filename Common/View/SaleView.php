<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

use InvalidArgumentException;

use function array_replace;
use function is_string;

/**
 * Class SaleView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleView extends AbstractView
{
    public string $locale   = 'en';
    public string $currency = 'USD';
    public string $template = '';
    public bool   $ati      = true;

    public ?LineView   $shipment;
    public TotalView   $gross;
    public TotalView   $final;
    public ?MarginView $commercialMargin = null;
    public ?MarginView $profitMargin     = null;

    /** @var array<int, LineView> */
    private array $items = [];
    /** @var array<int, LineView> */
    private array $discounts = [];
    /** @var array<int, TaxView> */
    private array $taxes = [];
    /** @var array<int, Button> */
    private array $buttons = [];
    /** @var array<int, string> */
    private array $alerts = [];
    /** @var array<int, string> */
    private array $messages = [];
    /** @var array<string, string> */
    private array $translations;

    public function __construct()
    {
        $this->translations = $this->getDefaultTranslations();

        $this->vars = [
            'buttons'           => [],
            'show_availability' => false,
            'show_taxes'        => false,
            'show_discount'     => false,
            'show_margin'       => false,
        ];
    }

    /**
     * Adds the item line.
     */
    public function addItem(LineView $line): void
    {
        $this->items[] = $line;
    }

    /**
     * Returns the items lines views.
     *
     * @return array<int, LineView>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Adds the discount line.
     */
    public function addDiscount(LineView $line): void
    {
        $this->discounts[] = $line;
    }

    /**
     * Returns the discounts lines views.
     *
     * @return array<int, LineView>
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    /**
     * Adds the tax view.
     */
    public function addTax(TaxView $view): void
    {
        $this->taxes[] = $view;
    }

    /**
     * Returns the taxes views.
     *
     * @return array<int, TaxView>
     */
    public function getTaxes(): array
    {
        return $this->taxes;
    }

    /**
     * Adds the button.
     */
    public function addButton(Button $button): void
    {
        $this->buttons[] = $button;
    }

    /**
     * @return array<int, Button>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * Adds the alert.
     */
    public function addAlert(string $alert): void
    {
        $this->alerts[] = $alert;
    }

    /**
     * Returns the alerts.
     *
     * @return array<int, string>
     */
    public function getAlerts(): array
    {
        return $this->alerts;
    }

    /**
     * Adds the message.
     */
    public function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * Returns the messages.
     *
     * @return array<int, string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Returns the translations.
     *
     * @return array<string, string>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Sets the translations.
     *
     * @param array<string, string> $translations
     */
    public function setTranslations(array $translations): void
    {
        foreach ($translations as $key => $string) {
            if (!(is_string($string) && !empty($string))) {
                throw new InvalidArgumentException("Invalid translation for key '$key'.");
            }
        }

        $this->translations = array_replace($this->getDefaultTranslations(), $translations);
    }

    public function getDefaultTranslations(): array
    {
        return [
            'designation'       => 'Designation',
            'reference'         => 'Reference',
            'availability'      => 'Avai.',
            'unit_net_price'    => 'Unit Price',
            'unit_ati_price'    => 'Unit Price',
            'quantity'          => 'Quantity',
            'net_gross'         => 'Gross',
            'ati_gross'         => 'Gross',
            'discount'          => 'Discount',
            'tax_rate'          => 'Tax rate',
            'tax_name'          => 'Tax',
            'tax_amount'        => 'Amount',
            'gross_totals'      => 'Gross totals',
            'net_total'         => 'Net total',
            'tax_total'         => 'Tax total',
            'grand_total'       => 'Grand total',
            'margin'            => 'Margin',
            'commercial_margin' => 'Commercial margin',
            'profit_margin'     => 'Profit margin',
        ];
    }
}
