<?php

namespace Ekyna\Component\Commerce\Order\Calculator;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Pricing\Total\Amount;
use Ekyna\Component\Commerce\Pricing\Total\AmountInterface;
use Ekyna\Component\Commerce\Pricing\Total\Amounts;
use Ekyna\Component\Commerce\Pricing\Total\AmountsInterface;

/**
 * Class Calculator
 * @package Ekyna\Component\Commerce\Order\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Calculator implements CalculatorInterface
{
    /**
     * @var string
     */
    private $mode = self::MODE_NET;


    /**
     * @inheritdoc
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function buildOrderItemAmounts(OrderItemInterface $item, AmountsInterface $amounts = null)
    {
        if (null === $amounts) {
            $amounts = new Amounts();
        }

        if ($item->hasChildren()) {
            $itemAmounts = new Amounts();

            $children = $item->getChildren()->toArray();
            foreach ($children as $child) {
                $this->buildOrderItemAmounts($child, $itemAmounts);
            }

            $itemAmounts->multiply($item->getQuantity());

            $amounts->merge($itemAmounts);
        } else {
            $amount = new Amount(
                // Don't round in gross mode (should not be displayed)
                $this->mode == self::MODE_NET ? $this->round($item->getNetPrice()) : $item->getNetPrice(),
                $item->getTaxRate(),
                $item->getTaxName()
            );

            $amount->multiply($item->getQuantity());

            $amounts->add($amount);
        }

        // TODO Percent adjustment applied to children if any

        return $amounts;
    }

    /**
     * @inheritdoc
     */
    public function buildOrderAmounts(OrderInterface $order, AmountsInterface $amounts = null)
    {
        if (null === $amounts) {
            $amounts = new Amounts();
        }

        // TODO

        return $amounts;
    }

    /**
     * @inheritdoc
     */
    public function calculateNetTotal($amounts)
    {
        $result = 0;

        if ($amounts instanceof AmountInterface) {
            $result = $amounts->getBase();
        } elseif ($amounts instanceof AmountsInterface) {
            foreach ($amounts->all() as $amount) {
                $result += $this->calculateNetTotal($amount);
            }
        } else {
            throw new \Exception('Expected instance of AmountInterface or AmountsInterface');
        }

        // Don't round in gross mode (should not be displayed)
        return $this->mode == self::MODE_NET ? $this->round($result) : $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateTaxTotal($amounts)
    {
        $result = 0;

        if ($amounts instanceof AmountInterface) {
            switch ($this->mode) {
                case self::MODE_NET :
                    // Net * TaxRate
                    $result = 0 != $amounts->getTaxRate()
                        ? $this->calculateNetTotal($amounts) * $amounts->getTaxRate() / 100
                        : $this->calculateNetTotal($amounts);
                    break;
                case self::MODE_GROSS :
                    // Total - Base
                    $result = $this->calculateGrossTotal($amounts) - $this->calculateNetTotal($amounts);
                    break;
                default:
                    throw new \Exception('Unexpected mode.');
            }
        } elseif ($amounts instanceof AmountsInterface) {
            foreach ($amounts->all() as $amount) {
                $result += $this->calculateTaxTotal($amount);
            }
        } else {
            throw new \Exception('Expected instance of AmountInterface or AmountsInterface');
        }

        return $this->round($result);
    }

    /**
     * @inheritdoc
     */
    public function calculateGrossTotal($amounts)
    {
        $result = 0;

        if ($amounts instanceof AmountInterface) {
            switch ($this->mode) {
                case self::MODE_NET :
                    // Net + Tax
                    $result = $this->calculateNetTotal($amounts) + $this->calculateTaxTotal($amounts);
                    break;
                case self::MODE_GROSS :
                    // Net * (TaxRate + 1)
                    $result = $this->calculateNetTotal($amounts) * (($amounts->getTaxRate() / 100) + 1);
                    break;
                default:
                    throw new \Exception('Unexpected mode.');
            }
        } elseif ($amounts instanceof AmountsInterface) {
            foreach ($amounts->all() as $amount) {
                $result += $this->calculateGrossTotal($amount);
            }
        } else {
            throw new \Exception('Expected instance of AmountInterface or AmountsInterface');
        }

        return $this->round($result);
    }

    /**
     * Rounds the results.
     *
     * @param float $result
     *
     * @return float
     */
    private function round($result)
    {
        // TODO precision based on currency
        return round($result, 2);
    }
}
