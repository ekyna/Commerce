<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface TaxGroupRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxGroupRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the default tax group.
     *
     * @return TaxGroupInterface
     */
    public function findDefault();
}
