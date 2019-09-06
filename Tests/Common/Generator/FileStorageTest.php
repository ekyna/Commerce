<?php

namespace Ekyna\Component\Commerce\Tests\Common\Generator;

use Ekyna\Component\Commerce\Common\Generator\FileStorage;
use PHPUnit\Framework\TestCase;

/**
 * Class FileStorageTest
 * @package Ekyna\Component\Commerce\Tests\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FileStorageTest extends TestCase
{
    /** @var string */
    private static $path;

    /** @var FileStorage  */
    private $storage;

    public static function setUpBeforeClass(): void
    {
        self::$path = sys_get_temp_dir() . '/storage';
    }

    protected function setUp(): void
    {
        if (is_file(self::$path)) {
            unlink(self::$path);
        }

        $this->storage = new FileStorage(self::$path, 3);
    }

    protected function tearDown(): void
    {
        $this->storage = null;
    }

    public function test_read(): void
    {
        file_put_contents(self::$path, 'foo');

        $this->assertEquals('foo', $this->storage->read());
    }

    public function test_write(): void
    {
        $this->storage->write('bar');

        $this->assertEquals('bar', file_get_contents(self::$path));
    }

    public function test_write_will_exists_with_content(): void
    {
        file_put_contents(self::$path, 'bar');

        $this->storage->write('foo');

        $this->assertEquals('foo', file_get_contents(self::$path));
    }
}
