<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class SaleView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleView extends AbstractView
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $ati;

    /**
     * @var TotalView
     */
    private $gross;

    /**
     * @var TotalView
     */
    private $final;

    /**
     * @var MarginView
     */
    private $margin;

    /**
     * @var LineView[]
     */
    private $items;

    /**
     * @var LineView[]
     */
    private $discounts;

    /**
     * @var LineView
     */
    private $shipment;

    /**
     * @var TaxView[]
     */
    private $taxes;

    /**
     * @var string[]
     */
    private $alerts;

    /**
     * @var string[]
     */
    private $messages;

    /**
     * @var array
     */
    private $translations;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ati = true;
        $this->items = [];
        $this->discounts = [];
        $this->taxes = [];
        $this->messages = [];
        $this->alerts = [];

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
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return SaleView
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the currency.
     *
     * @param string $currency
     *
     * @return SaleView
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Sets the template.
     *
     * @param string $template
     *
     * @return SaleView
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Returns the template.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Sets the ati.
     *
     * @param bool $ati
     *
     * @return SaleView
     */
    public function setAti(bool $ati): self
    {
        $this->ati = $ati;

        return $this;
    }

    /**
     * Returns the ati.
     *
     * @return bool
     */
    public function isAti(): bool
    {
        return $this->ati;
    }

    /**
     * Sets the gross.
     *
     * @param TotalView $gross
     *
     * @return SaleView
     */
    public function setGross(TotalView $gross): self
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * Returns the gross total view.
     *
     * @return TotalView
     */
    public function getGross(): TotalView
    {
        return $this->gross;
    }

    /**
     * Sets the final.
     *
     * @param TotalView $final
     *
     * @return SaleView
     */
    public function setFinal(TotalView $final): self
    {
        $this->final = $final;

        return $this;
    }

    /**
     * Returns the final total view.
     *
     * @return TotalView
     */
    public function getFinal(): TotalView
    {
        return $this->final;
    }

    /**
     * Sets the margin view.
     *
     * @param MarginView|null $margin
     *
     * @return SaleView
     */
    public function setMargin(MarginView $margin = null): self
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * Returns the margin view.
     *
     * @return MarginView
     */
    public function getMargin(): ?MarginView
    {
        return $this->margin;
    }

    /**
     * Adds the item line.
     *
     * @param LineView $line
     *
     * @return SaleView
     */
    public function addItem(LineView $line): self
    {
        $this->items[] = $line;

        return $this;
    }

    /**
     * Returns the items lines views.
     *
     * @return LineView[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Adds the discount line.
     *
     * @param LineView $line
     *
     * @return SaleView
     */
    public function addDiscount(LineView $line): self
    {
        $this->discounts[] = $line;

        return $this;
    }

    /**
     * Returns the discounts lines views.
     *
     * @return LineView[]
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    /**
     * Sets the shipment line.
     *
     * @param LineView $line
     *
     * @return SaleView
     */
    public function setShipment(LineView $line): self
    {
        $this->shipment = $line;

        return $this;
    }

    /**
     * Returns the shipment line view.
     *
     * @return LineView|null
     */
    public function getShipment(): ?LineView
    {
        return $this->shipment;
    }

    /**
     * Adds the tax view.
     *
     * @param TaxView $view
     *
     * @return SaleView
     */
    public function addTax(TaxView $view): self
    {
        $this->taxes[] = $view;

        return $this;
    }

    /**
     * Returns the taxes views.
     *
     * @return TaxView[]
     */
    public function getTaxes(): array
    {
        return $this->taxes;
    }

    /**
     * Adds the button.
     *
     * @param Button $button
     *
     * @return SaleView
     */
    public function addButton(Button $button): self
    {
        $this->vars['buttons'][] = $button;

        return $this;
    }

    /**
     * Adds the alert.
     *
     * @param string $alert
     *
     * @return SaleView
     */
    public function addAlert(string $alert): self
    {
        $this->alerts[] = $alert;

        return $this;
    }

    /**
     * Returns the alerts.
     *
     * @return string[]
     */
    public function getAlerts(): array
    {
        return $this->alerts;
    }

    /**
     * Adds the message.
     *
     * @param string $message
     *
     * @return SaleView
     */
    public function addMessage(string $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Returns the messages.
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Returns the translations.
     *
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Sets the translations.
     *
     * @param array $translations
     *
     * @return SaleView
     */
    public function setTranslations(array $translations): self
    {
        foreach ($translations as $key => $string) {
            if (!(is_string($string) && !empty($string))) {
                throw new \InvalidArgumentException("Invalid translation for key '$key'.");
            }
        }

        $this->translations = array_replace($this->getDefaultTranslations(), $translations);

        return $this;
    }

    /**
     * Returns the default translations.
     *
     * @return array
     */
    public function getDefaultTranslations(): array
    {
        return [
            'designation'    => 'Designation',
            'reference'      => 'Reference',
            'availability'   => 'Avai.',
            'unit_net_price' => 'Unit Price',
            'unit_ati_price' => 'Unit Price',
            'quantity'       => 'Quantity',

            'net_gross'      => 'Gross',
            'ati_gross'      => 'Gross',
            'discount'       => 'Discount',

            'tax_rate'       => 'Tax rate',
            'tax_name'       => 'Tax',
            'tax_amount'     => 'Amount',

            'gross_totals'   => 'Gross totals',
            'net_total'      => 'Net total',
            'tax_total'      => 'Tax total',
            'grand_total'    => 'Grand total',
            'margin'         => 'Margin',
        ];
    }
}
