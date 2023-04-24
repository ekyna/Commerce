<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Generator;

use function intval;
use function str_pad;
use function strlen;
use function substr;

/**
 * Class AbstractGenerator
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    protected StorageInterface $storage;

    /**
     * Constructor.
     *
     * @param int    $length The total number length
     * @param string $prefix The number prefix
     * @param bool   $debug
     */
    public function __construct(
        protected readonly int    $length = 10,
        protected readonly string $prefix = '',
        protected readonly bool   $debug = false
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setStorage(string|StorageInterface $storage): void
    {
        if ($storage instanceof StorageInterface) {
            $this->storage = $storage;

            return;
        }

        $this->storage = new FileStorage($storage, $this->length);
    }

    /**
     * @inheritDoc
     */
    public function generate(object $subject): string
    {
        $number = $this->storage->read($subject);

        $number = $this->increment($number);

        $this->storage->write($number);

        return $number;
    }

    /**
     * Returns the prefix.
     *
     * @return string
     */
    protected function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Generates the number.
     *
     * @param string $number
     *
     * @return string
     */
    protected function increment(string $number): string
    {
        if (!empty($prefix = $this->getPrefix())) {
            if (str_starts_with($number, $prefix)) {
                $number = intval(substr($number, strlen($prefix)));
            } else {
                $number = 0;
            }
        } else {
            $number = intval($number);
        }

        /*if ($this->debug) {
            $test = intval(str_pad('9', $this->length - strlen($prefix) - 1, '9'));
            if ($test > $number) {
                $number = $test;
            }
        }*/

        return $prefix . str_pad((string)($number + 1), $this->length - strlen($prefix), '0', STR_PAD_LEFT);
    }
}
