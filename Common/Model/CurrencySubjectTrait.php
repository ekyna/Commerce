<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait CurrencySubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait CurrencySubjectTrait
{
    protected ?CurrencyInterface $currency = null;


    public function getCurrency(): ?CurrencyInterface
    {
        return $this->currency;
    }

    /**
     * @return $this|CurrencySubjectInterface
     */
    public function setCurrency(?CurrencyInterface $currency): CurrencySubjectInterface
    {
        $this->currency = $currency;

        return $this;
    }
}
