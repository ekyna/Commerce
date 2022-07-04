<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface CurrencyRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<CurrencyInterface>
 */
interface CurrencyRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Sets the default code.
     *
     * @param string $code
     */
    public function setDefaultCode(string $code): void;

    /**
     * Returns the default currency.
     *
     * @return CurrencyInterface
     */
    public function findDefault(): CurrencyInterface;

    /**
     * Finds a currency by its code.
     *
     * @param string $code
     *
     * @return CurrencyInterface|null
     */
    public function findOneByCode(string $code): ?CurrencyInterface;

    /**
     * Finds the codes of the enabled currencies.
     *
     * @return array|string[]
     */
    public function findEnabledCodes(): array;

    /**
     * Finds all the currencies codes.
     *
     * @return array|string[]
     */
    public function findAllCodes(): array;
}
