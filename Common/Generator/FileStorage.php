<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Generator;

use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class FileStorage
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FileStorage implements StorageInterface
{
    /**
     * @var resource
     */
    protected $handle;

    public function __construct(
        private readonly string $path,
        private readonly int    $length
    ) {
    }

    /**
     * Reads the previous number.
     *
     * @return string
     */
    public function read(): string
    {
        // Open
        if (false === $this->handle = fopen($this->path, 'c+')) {
            throw new RuntimeException("Failed to open file $this->path.");
        }
        // Read
        if (false === $data = fread($this->handle, $this->length)) {
            throw new RuntimeException("Failed to read file $this->path.");
        }
        // Close
        fclose($this->handle);

        return $data;
    }

    /**
     * Writes the new data.
     *
     * @param string $data
     */
    public function write(string $data): void
    {
        // Open
        if (false === $this->handle = fopen($this->path, 'c+')) {
            throw new RuntimeException("Failed to open file $this->path.");
        }
        // Lock
        if (!flock($this->handle, LOCK_EX)) {
            throw new RuntimeException("Failed to lock file $this->path.");
        }
        // Truncate
        if (!ftruncate($this->handle, 0)) {
            throw new RuntimeException("Failed to truncate file $this->path.");
        }
        // Reset
        if (0 > fseek($this->handle, 0)) {
            throw new RuntimeException("Failed to move pointer at the beginning of the file $this->path.");
        }
        // Write
        if (!fwrite($this->handle, $data)) {
            throw new RuntimeException("Failed to write file $this->path.");
        }
        // Flush
        if (!fflush($this->handle)) {
            throw new RuntimeException("Failed to flush file $this->path.");
        }
        // Unlock
        if (!flock($this->handle, LOCK_UN)) {
            throw new RuntimeException("Failed to unlock file $this->path.");
        }
        // Close
        fclose($this->handle);
    }
}
