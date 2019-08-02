<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class StockSubjectStates
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockSubjectStates
{
    const STATE_IN_STOCK     = 'in_stock';
    const STATE_PRE_ORDER    = 'pre_order';
    const STATE_OUT_OF_STOCK = 'out_of_stock';


    /**
     * Returns all the states.
     *
     * @return string[]
     */
    static public function getStates(): array
    {
        return [
            self::STATE_IN_STOCK,
            self::STATE_PRE_ORDER,
            self::STATE_OUT_OF_STOCK,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param string $state
     * @param bool   $throwException
     *
     * @return bool
     */
    static public function isValidState(string $state, bool $throwException = false): bool
    {
        if (in_array($state, self::getStates(), true)) {
            return true;
        }

        if ($throwException) {
            throw new UnexpectedValueException("Unknown state '$state'.");
        }

        return false;
    }

    /**
     * Returns whether the state A has a better availability than the state B.
     *
     * @param string $stateA
     * @param string $stateB
     *
     * @return bool
     */
    static public function isBetterState(string $stateA, string $stateB): bool
    {
        self::isValidState($stateA, true);
        self::isValidState($stateB, true);

        if ($stateA === self::STATE_IN_STOCK) {
            return $stateB !== self::STATE_IN_STOCK;
        } elseif ($stateA === self::STATE_PRE_ORDER) {
            return $stateB === self::STATE_OUT_OF_STOCK;
        }

        return false;
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
