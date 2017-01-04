<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface CurrencySubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencySubjectInterface
{
    /**
     * Returns the currency.
     *
     * @return CurrencyInterface
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|CurrencySubjectInterface
     */
    public function setCurrency(CurrencyInterface $currency);
}
