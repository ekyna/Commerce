<?php

namespace Ekyna\Component\Commerce\Pricing\Total;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Amounts
 * @package Ekyna\Component\Commerce\Pricing\Total
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Amounts implements AmountsInterface
{
    /**
     * @var ArrayCollection|AmountInterface[]
     */
    private $amounts;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->amounts = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        return $this->amounts->toArray();
    }

    /**
     * @inheritdoc
     */
    public function has(AmountInterface $amount)
    {
        return null !== $this->find($amount);
    }

    /**
     * @inheritdoc
     */
    public function add(AmountInterface $amount)
    {
        if ($this->has($amount)) {
            $this->merge($amount);
        } else {
            $this->amounts->add($amount);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function remove(AmountInterface $amount)
    {
        if (!$this->has($amount)) {
            return $this;
        }

        $a = $this->find($amount);
        if ($a->getBase() > $amount->getBase()) {
            $a->removeBase($amount->getBase());
        } else {
            $this->amounts = $this->amounts->filter(function(AmountInterface $a) use ($amount) {
                return !$a->equals($amount);
            });
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function merge($amounts)
    {
        if ($amounts instanceof AmountInterface) {
            if (null === $a = $this->find($amounts)) {
                throw new \Exception('Failed to merge amount.');
            }
            $a->merge($amounts);
        } elseif ($amounts instanceof AmountsInterface) {
            foreach ($amounts->all() as $amount) {
                $this->add($amount);
            }
        } else {
            throw new \Exception('Unexpected amount.');
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function multiply($quantity)
    {
        foreach ($this->amounts as $amount) {
            $amount->multiply($quantity);
        }

        return $this;
    }

    /**
     * Finds the tax amount.
     *
     * @param AmountInterface $amount
     *
     * @return AmountInterface|null
     */
    protected function find(AmountInterface $amount)
    {
        foreach ($this->amounts as $a) {
            if ($a->equals($amount)) {
                return $a;
            }
        }

        return null;
    }
}
