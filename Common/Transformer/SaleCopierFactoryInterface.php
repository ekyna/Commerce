<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleCopierFactoryInterface
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleCopierFactoryInterface
{
    public function create(Model\SaleInterface $source, Model\SaleInterface $target): SaleCopierInterface;
}
