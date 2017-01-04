<?php

namespace Ekyna\Component\Commerce\Quote\Repository;

use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Interface QuoteRepositoryInterface
 * @package Ekyna\Component\Commerce\Quote\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuoteInterface|null findOneById($id)
 * @method QuoteInterface|null findOneByKey($key)
 */
interface QuoteRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Creates a new quote instance.
     *
     * @return QuoteInterface
     */
    public function createNew();
}
