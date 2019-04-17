<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface CurrencyRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyRepositoryInterface extends ResourceRepositoryInterface
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

    /**
     * Finds the codes of the enabled currencies.
     *
     * @return array|string[]
     */
    public function findEnabledCodes();

    /**
     * Finds all the currencies codes.
     *
     * @return array|string[]
     */
    public function findAllCodes();
}
