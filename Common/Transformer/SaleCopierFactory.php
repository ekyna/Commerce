<?php

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
    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * Constructor.
     *
     * @param SaleFactoryInterface $saleFactory
     */
    public function __construct(SaleFactoryInterface $saleFactory)
    {
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(Model\SaleInterface $source, Model\SaleInterface $target)
    {
        return new SaleCopier($this->saleFactory, $source, $target);
    }
}
