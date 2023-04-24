<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Generator;

/**
 * Class StorageInterface
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StorageInterface
{
    /**
     * Reads the previous data.
     *
     * @return string
     */
    public function read(): string;

    /**
     * Writes the new data.
     *
     * @param string $data
     */
    public function write(string $data): void;
}
