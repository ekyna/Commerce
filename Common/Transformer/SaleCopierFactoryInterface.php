<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleCopierFactoryInterface
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleCopierFactoryInterface
{
    /**
     * Returns a sale copier.
     *
     * @param Model\SaleInterface $source
     * @param Model\SaleInterface $target
     *
     * @return SaleCopierInterface
     */
    public function create(Model\SaleInterface $source, Model\SaleInterface $target);
}
