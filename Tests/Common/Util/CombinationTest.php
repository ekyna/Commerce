<?php

namespace Ekyna\Component\Commerce\Tests\Common\Util;

use Ekyna\Component\Commerce\Common\Util\Combination;
use PHPUnit\Framework\TestCase;

/**
 * Class CombinationTest
 * @package Ekyna\Component\Commerce\Tests\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CombinationTest extends TestCase
{
    /**
     * @param array $expected
     * @param array $values
     * @param int   $length
     *
     * @dataProvider provide_generate
     */
    public function test_generate(array $expected, array $values, int $length = null): void
    {
        foreach (Combination::generate($values, $length) as $result) {
            $this->assertEquals(current($expected), $result);
            next($expected);
        }
    }

    public function provide_generate(): array
    {
        return [
            [
                [[1], [2], [3], [4]],
                [1, 2, 3, 4],
                1,
            ],
            [
                [[1, 2], [1, 3], [1, 4], [2, 3], [2, 4], [3, 4]],
                [1, 2, 3, 4],
                2,
            ],
            [
                [[1, 2, 3], [1, 2, 4], [1, 3, 4], [2, 3, 4]],
                [1, 2, 3, 4],
                3,
            ],
            [
                [[1, 2, 3, 4]],
                [1, 2, 3, 4],
                4,
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                    [4],
                    [1, 2],
                    [1, 3],
                    [1, 4],
                    [2, 3],
                    [2, 4],
                    [3, 4],
                    [1, 2, 3],
                    [1, 2, 4],
                    [1, 3, 4],
                    [2, 3, 4],
                    [1, 2, 3, 4],
                ],
                [1, 2, 3, 4],
                null,
            ],
        ];
    }

    /**
     * @param array $expected
     * @param array $values
     * @param int   $length
     *
     * @dataProvider provide_generateAssoc
     */
    public function test_generateAssoc(array $expected, array $values, int $length = null): void
    {
        foreach (Combination::generateAssoc($values, $length) as $result) {
            $this->assertEquals(current($expected), $result);
            next($expected);
        }
    }

    public function provide_generateAssoc(): array
    {
        return [
            [
                [['a' => 1], ['b' => 2], ['c' => 3]],
                ['a' => 1, 'b' => 2, 'c' => 3],
                1,
            ],
            [
                [
                    ['a' => 1, 'b' => 2],
                    ['a' => 1, 'c' => 3],
                    ['b' => 2, 'c' => 3],
                ],
                ['a' => 1, 'b' => 2, 'c' => 3],
                2,
            ],
            [
                [['a' => 1, 'b' => 2, 'c' => 3]],
                ['a' => 1, 'b' => 2, 'c' => 3],
                3,
            ],
            [
                [
                    ['a' => 1],
                    ['b' => 2],
                    ['c' => 3],
                    ['a' => 1, 'b' => 2],
                    ['a' => 1, 'c' => 3],
                    ['b' => 2, 'c' => 3],
                    ['a' => 1, 'b' => 2, 'c' => 3],
                ],
                ['a' => 1, 'b' => 2, 'c' => 3],
                null,
            ],
        ];
    }
}