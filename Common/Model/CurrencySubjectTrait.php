<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait CurrencySubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait CurrencySubjectTrait
{
    /**
     * @var CurrencyInterface
     */
    protected $currency;


    /**
     * Returns the currency.
     *
     * @return CurrencyInterface|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|CurrencySubjectInterface
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }
}
