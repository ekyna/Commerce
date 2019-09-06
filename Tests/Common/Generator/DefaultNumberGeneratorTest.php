<?php

namespace Ekyna\Component\Commerce\Tests\Common\Generator;

use Ekyna\Component\Commerce\Common\Generator\DefaultGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultNumberGeneratorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultNumberGeneratorTest extends TestCase
{
    /** @var string */
    private static $path;

    /** @var DefaultGenerator */
    private $generator;

    public static function setUpBeforeClass(): void
    {
        self::$path = sys_get_temp_dir() . '/storage';
    }

    protected function setUp(): void
    {
        if (is_file(self::$path)) {
            unlink(self::$path);
        }

        $this->generator = new DefaultGenerator(self::$path, 6, 'FOO');
    }

    protected function tearDown(): void
    {
        $this->generator = null;
    }

    public function test_generator(): void
    {
        $subject = new \stdClass();

        // Empty file
        $this->assertEquals('FOO001', $this->generator->generate($subject));

        // File has previous number
        $this->assertEquals('FOO002', $this->generator->generate($subject));
    }
}
