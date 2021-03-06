<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface TaxRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface TaxRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the tax by its code.
     *
     * @param string $code
     *
     * @return TaxInterface|null
     */
    public function findOneByCode(string $code): ?TaxInterface;
}
