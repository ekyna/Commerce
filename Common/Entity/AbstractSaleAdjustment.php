<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Calculator\Amount;
use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;

/**
 * Class AbstractSaleAdjustment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleAdjustment extends AbstractAdjustment implements SaleAdjustmentInterface
{
    /**
     * @var Amount
     */
    private $result;

    /**
     * @inheritdoc
     */
    public function clearResult()
    {
        $this->result = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setResult(Amount $result)
    {
        $this->result = $result;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        return $this->result;
    }
}
