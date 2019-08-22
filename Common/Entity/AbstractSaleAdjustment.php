<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Class AbstractSaleAdjustment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleAdjustment extends AbstractAdjustment implements Model\SaleAdjustmentInterface
{
    /**
     * @var Model\Amount[]
     */
    protected $results = [];


    /**
     * @inheritdoc
     */
    public function clearResults(): Model\SaleAdjustmentInterface
    {
        $this->results = [];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResult(string $currency): ?Model\Amount
    {
        return $this->results[$currency] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setResult(Model\Amount $result): Model\SaleAdjustmentInterface
    {
        $this->results[$result->getCurrency()] = $result;

        return $this;
    }
}
