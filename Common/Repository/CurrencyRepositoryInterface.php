<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;

/**
 * Interface CurrencyRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyRepositoryInterface
{
    /**
     * Returns the default currency.
     *
     * @return CurrencyInterface
     */
    public function findDefault();

    /**
     * Finds a currency by its code.
     *
     * @param string $code
     *
     * @return CurrencyInterface
     */
    public function findOneByCode($code);
}
