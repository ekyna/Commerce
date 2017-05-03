<?php

namespace Ekyna\Component\Commerce\Credit\Entity;

use Ekyna\Component\Commerce\Credit\Model;

/**
 * Class AbstractCreditItem
 * @package Ekyna\Component\Commerce\Credit\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCreditItem implements Model\CreditItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\CreditInterface
     */
    protected $credit;

    /**
     * @var float
     */
    protected $quantity = 0;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @inheritdoc
     */
    public function setCredit(Model\CreditInterface $credit = null)
    {
        if ($this->credit !== $credit) {
            $previous = $this->credit;
            $this->credit = $credit;

            if ($previous) {
                $previous->removeItem($this);
            }

            if ($this->credit) {
                $this->credit->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}
