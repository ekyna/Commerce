<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;

/**
 * Class SaleCopierFactory
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCopierFactory implements SaleCopierFactoryInterface
{
    protected SaleFactoryInterface $saleFactory;


    public function __construct(SaleFactoryInterface $saleFactory)
    {
        $this->saleFactory = $saleFactory;
    }

    public function create(Model\SaleInterface $source, Model\SaleInterface $target): SaleCopierInterface
    {
        return new SaleCopier($this->saleFactory, $source, $target);
    }
}
