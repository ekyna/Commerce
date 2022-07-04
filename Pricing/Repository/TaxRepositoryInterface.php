<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface TaxRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<TaxInterface>
 */
interface TaxRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the tax by its code.
     */
    public function findOneByCode(string $code): ?TaxInterface;
}
