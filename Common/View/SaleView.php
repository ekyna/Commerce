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
    private $template;

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
     * @var array
     */
    private $translations;


    /**
     * Constructor.
     *
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;

        $this->items = [];
        $this->discounts = [];
        $this->taxes = [];

        $this->translations = $this->getDefaultTranslations();

        $this->vars = [
            'show_availability' => false,
            'show_taxes'        => false,
            'show_discount'     => false,
            'show_margin'       => false,
        ];
    }

    /**
     * Returns the template.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the gross.
     *
     * @param TotalView $gross
     *
     * @return SaleView
     */
    public function setGross(TotalView $gross)
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * Returns the gross total view.
     *
     * @return TotalView
     */
    public function getGross()
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
    public function setFinal(TotalView $final)
    {
        $this->final = $final;

        return $this;
    }

    /**
     * Returns the final total view.
     *
     * @return TotalView
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Sets the margin view.
     *
     * @param MarginView $margin
     *
     * @return SaleView
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * Returns the margin view.
     *
     * @return MarginView
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * Adds the item line.
     *
     * @param LineView $line
     *
     * @return $this
     */
    public function addItem(LineView $line)
    {
        $this->items[] = $line;

        return $this;
    }

    /**
     * Returns the items lines views.
     *
     * @return LineView[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Adds the discount line.
     *
     * @param LineView $line
     *
     * @return $this
     */
    public function addDiscount(LineView $line)
    {
        $this->discounts[] = $line;

        return $this;
    }

    /**
     * Returns the discounts lines views.
     *
     * @return LineView[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Sets the shipment line.
     *
     * @param LineView $line
     */
    public function setShipment(LineView $line)
    {
        $this->shipment = $line;
    }

    /**
     * Returns the shipment line view.
     *
     * @return LineView|null
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * Adds the tax view.
     *
     * @param TaxView $view
     *
     * @return $this
     */
    public function addTax(TaxView $view)
    {
        $this->taxes[] = $view;

        return $this;
    }

    /**
     * Returns the taxes views.
     *
     * @return TaxView[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * Returns the translations.
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Sets the translations.
     *
     * @param array $translations
     */
    public function setTranslations(array $translations)
    {
        foreach ($translations as $key => $string) {
            if (!(is_string($string) && !empty($string))) {
                throw new \InvalidArgumentException("Invalid translation for key '$key'.");
            }
        }

        $this->translations = array_replace($this->getDefaultTranslations(), $translations);
    }

    /**
     * Returns the default translations.
     *
     * @return array
     */
    public function getDefaultTranslations()
    {
        return [
            'designation'    => 'Designation',
            'reference'      => 'Reference',
            'availability'   => 'Avai.',
            'unit_net_price' => 'Unit Price',
            'quantity'       => 'Quantity',

            'gross'          => 'Gross',
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
