<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface TaxGroupRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<TaxGroupInterface>
 */
interface TaxGroupRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the default tax group.
     *
     * @param bool $throwException Whether to throw exception if not found.
     */
    public function findDefault(bool $throwException = true): ?TaxGroupInterface;

    /**
     * Returns the tax group by its code.
     */
    public function findOneByCode(string $code): ?TaxGroupInterface;
}
