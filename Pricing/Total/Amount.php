<?php

namespace Ekyna\Component\Commerce\Pricing\Total;

/**
 * Class Amount
 * @package Ekyna\Component\Commerce\Pricing\Total
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Amount implements AmountInterface
{
    /**
     * @var float
     */
    protected $base;

    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @var string
     */
    protected $taxName;


    /**
     * Constructor.
     *
     * @param float  $base
     * @param float  $taxRate
     * @param string $taxName
     */
    public function __construct($base = 0.0, $taxRate = 0.0, $taxName = null)
    {
        $this->base = floatval($base);
        $this->taxRate = floatval($taxRate);
        $this->taxName = $taxName;
    }

    /**
     * @inheritdoc
     */
    public function getTaxName()
    {
        return $this->taxName;
    }

    /**
     * @inheritdoc
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @inheritdoc
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @inheritdoc
     */
    public function addBase($base)
    {
        $this->base += floatval($base);
    }

    /**
     * @inheritdoc
     */
    public function removeBase($base)
    {
        $this->base -= floatval($base);
    }

    /**
     * @inheritdoc
     */
    public function equals(AmountInterface $amount)
    {
        return $this->taxName === $amount->getTaxName()
            && $this->taxRate === $amount->getTaxRate();
    }

    /**
     * @inheritdoc
     */
    public function merge(AmountInterface $amount)
    {
        if (!$this->equals($amount)) {
            throw new \Exception('Failed to merge amount.');
        }

        $this->addBase($amount->getBase());
    }

    /**
     * @inheritdoc
     */
    public function multiply($quantity)
    {
        $this->base *= $quantity;
    }
}
