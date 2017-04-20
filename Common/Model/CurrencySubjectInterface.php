<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface CurrencySubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencySubjectInterface
{
    public function getCurrency(): ?CurrencyInterface;

    public function setCurrency(?CurrencyInterface $currency): CurrencySubjectInterface;
}
