<?php

namespace Ekyna\Component\Commerce\Common\Util;

/**
 * Class Combination
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Combination
{
    /**
     * Returns the unique combinations for the given values.
     *
     * @param array $values
     * @param int   $length
     *
     * @return \Generator
     */
    public static function generate(array $values, int $length = null): \Generator
    {
        if (is_null($length)) {
            for ($l = 1; $l <= count($values); $l++) {
                yield from self::generate($values, $l);
            }

            return;
        }

        $original = count($values);
        $remaining = $original - $length + 1;

        for ($i = 0; $i < $remaining; ++$i) {
            $current = $values[$i];

            if (1 === $length) {
                yield [$current];

                continue;
            }

            $subSet = array_slice($values, $i + 1);
            foreach (self::generate($subSet, $length - 1) as $combination) {
                array_unshift($combination, $current);
                yield $combination;
            }
        }
    }

    /**
     * Returns unique associative combinations for the given values.
     *
     * @param array    $values
     * @param int|null $length
     *
     * @return \Generator
     */
    public static function generateAssoc(array $values, int $length = null): \Generator
    {
        if (is_null($length)) {
            for ($l = 1; $l <= count($values); $l++) {
                yield from self::generateAssoc($values, $l);
            }

            return;
        }

        foreach (self::generate(array_keys($values), $length) as $combination) {
            $result = [];

            foreach ($combination as $key) {
                $result[$key] = $values[$key];
            }

            yield $result;
        }
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
