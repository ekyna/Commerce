<?php

namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class DefaultNumberGenerator
 * @package Ekyna\Component\Commerce\Order\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultNumberGenerator implements NumberGeneratorInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $length;


    /**
     * Constructor.
     *
     * @param string $filePath The number file path
     * @param string $prefix   The number prefix
     * @param int    $length   The total number length
     */
    public function __construct($filePath, $prefix = 'ym', $length = 10)
    {
        $this->filePath = $filePath;
        $this->prefix = $prefix;
        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(NumberSubjectInterface $subject)
    {
        if (null !== $subject->getNumber()) {
            return $this;
        }

        $number = $this->readNumber();

        $number = $this->generateNumber($number);

        $this->writeNumber($number);

        $subject->setNumber($number);

        return $this;
    }

    /**
     * Generates the number.
     *
     * @param string $number
     *
     * @return string
     */
    protected function generateNumber($number)
    {
        if (!empty($this->prefix)) {
            if (0 !== strpos($number, $this->prefix)) {
                $number = 0;
            } else {
                $number = intval(substr($number, strlen($this->prefix)));
            }
        } else {
            $number = intval($number);
        }

        return $this->prefix . str_pad($number + 1, 10 - strlen($this->prefix), '0', STR_PAD_LEFT);
    }

    /**
     * Reads the previous number.
     *
     * @return bool|string
     */
    private function readNumber()
    {
        // Open
        if (false === $this->handle = fopen($this->filePath, 'c+')) {
            throw new RuntimeException("Failed to open file {$this->filePath}.");
        }
        // Exclusive lock
        if (!flock($this->handle, LOCK_EX)) {
            throw new RuntimeException("Failed to lock file {$this->filePath}.");
        }

        return fread($this->handle, $this->length);
    }

    /**
     * Writes the previous number.
     *
     * @param string $number
     */
    private function writeNumber($number)
    {
        // Truncate
        if (!ftruncate($this->handle, 0)) {
            throw new RuntimeException("Failed to truncate file {$this->filePath}.");
        }
        // Reset
        if (0 > fseek($this->handle, 0)) {
            throw new RuntimeException("Failed to move pointer at the beginning of the file {$this->filePath}.");
        }
        // Write
        if (!fwrite($this->handle, $number)) {
            throw new RuntimeException("Failed to write file {$this->filePath}.");
        }
        // Flush
        if (!fflush($this->handle)) {
            throw new RuntimeException("Failed to flush file {$this->filePath}.");
        }
        // Unlock
        if (!flock($this->handle, LOCK_UN)) {
            throw new RuntimeException("Failed to unlock file {$this->filePath}.");
        }
        // Close
        fclose($this->handle);
    }
}
