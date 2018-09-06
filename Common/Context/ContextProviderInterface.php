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
     * @param SaleInterface $sale The sale to build the context for, if any.
     *
     * @return ContextInterface
     */
    public function getContext(SaleInterface $sale = null);

    /**
     * Sets the context and fills empty properties with default values.
     *
     * @param ContextInterface|SaleInterface $contextOrSale
     *
     * @return ContextProviderInterface
     */
    public function setContext($contextOrSale);
}
