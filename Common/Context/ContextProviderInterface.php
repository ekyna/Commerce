<?php

namespace Ekyna\Component\Commerce\Common\Context;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface ContextProviderInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ContextProviderInterface
{
    /**
     * Returns the context.
     *
     * @param SaleInterface $sale     The sale to build the context for, if any.
     * @param bool          $fallback Whether to fallback to logged in customer.
     *
     * @return ContextInterface
     */
    public function getContext(SaleInterface $sale = null, $fallback = true);
}
