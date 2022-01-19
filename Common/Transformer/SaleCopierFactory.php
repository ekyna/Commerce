<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model;

/**
 * Class SaleCopierFactory
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCopierFactory implements SaleCopierFactoryInterface
{
    protected FactoryHelperInterface $factoryHelper;


    public function __construct(FactoryHelperInterface $factoryHelper)
    {
        $this->factoryHelper = $factoryHelper;
    }

    public function create(Model\SaleInterface $source, Model\SaleInterface $target): SaleCopierInterface
    {
        return new SaleCopier($this->factoryHelper, $source, $target);
    }
}
