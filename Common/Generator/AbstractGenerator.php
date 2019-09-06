<?php

namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class AbstractGenerator
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var bool
     */
    protected $debug;


    /**
     * Constructor.
     *
     * @param string $path   The number file path
     * @param int    $length The total number length
     * @param string $prefix The number prefix
     * @param bool   $debug
     */
    public function __construct(string $path, int $length = 10, string $prefix = '', bool $debug = false)
    {
        $this->setStorage($path, $length);

        $this->length = $length;
        $this->prefix = $prefix;
        $this->debug = $debug;
    }

    /**
     * @inheritDoc
     */
    public function setStorage($storage, int $length = null): void
    {
        if ($storage instanceof StorageInterface) {
            $this->storage = $storage;

            return;
        }

        if (is_string($storage)) {
            if (0 > $length) {
                throw new InvalidArgumentException("Length must be greater than zero.");
            }

            $this->storage = new FileStorage($storage, $length);

            return;
        }

        throw new UnexpectedTypeException($storage, ['string', StorageInterface::class]);
    }

    /**
     * @inheritDoc
     */
    public function generate(object $subject): string
    {
        $number = $this->storage->read();

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
            if (0 !== strpos($number, $prefix)) {
                $number = 0;
            } else {
                $number = intval(substr($number, strlen($prefix)));
            }
        } else {
            $number = intval($number);
        }

        if ($this->debug && 999999 > $number) {
            $number = 999999;
        }

        return $prefix . str_pad($number + 1, $this->length - strlen($prefix), '0', STR_PAD_LEFT);
    }
}
