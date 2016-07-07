<?php

namespace Ekyna\Component\Commerce\Order\Calculator;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Pricing\Total\AmountInterface;
use Ekyna\Component\Commerce\Pricing\Total\AmountsInterface;

/**
 * Interface CalculatorInterface
 * @package Ekyna\Component\Commerce\Order\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CalculatorInterface
{
    /**
     * Calculate based on the net price.
     */
    const MODE_NET = 'net';

    /**
     * Calculate based on the gross price.
     */
    const MODE_GROSS = 'gross';


    /**
     * Sets the calculation mode.
     *
     * @param string $mode
     *
     * @return $this|CalculatorInterface
     */
    public function setMode($mode);

    /**
     * Builds the order item total.
     *
     * @param OrderItemInterface $item
     * @param AmountsInterface   $amounts
     *
     * @return AmountsInterface
     */
    public function buildOrderItemAmounts(OrderItemInterface $item, AmountsInterface $amounts = null);

    /**
     * Builds the order total.
     *
     * @param OrderInterface   $order
     * @param AmountsInterface $amounts
     *
     * @return AmountsInterface
     */
    public function buildOrderAmounts(OrderInterface $order, AmountsInterface $amounts = null);


    /**
     * Calculates the net total.
     *
     * @param AmountInterface|AmountsInterface $amounts
     *
     * @return float
     */
    public function calculateNetTotal($amounts);

    /**
     * Calculates the tax total.
     *
     * @param AmountInterface|AmountsInterface $amounts
     *
     * @return float
     */
    public function calculateTaxTotal($amounts);

    /**
     * Calculate the gross total.
     *
     * @param AmountInterface|AmountsInterface $amounts
     *
     * @return float
     */
    public function calculateGrossTotal($amounts);
}
