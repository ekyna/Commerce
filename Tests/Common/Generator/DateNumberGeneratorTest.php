<?php

namespace Ekyna\Component\Commerce\Tests\Common\Generator;

use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Class DateNumberGeneratorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DateNumberGeneratorTest extends TestCase
{
    /** @var string */
    private static $path;

    /** @var DateNumberGenerator */
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

        $this->generator = new DateNumberGenerator(self::$path, 8, 'md_');
    }

    protected function tearDown(): void
    {
        $this->generator = null;
    }

    public function test_generator(): void
    {
        $prefix = date('md_');

        $subject = new \stdClass();

        // Empty file
        $this->assertEquals($prefix.'001', $this->generator->generate($subject));

        // File has previous number
        $this->assertEquals($prefix.'002', $this->generator->generate($subject));
    }
}
