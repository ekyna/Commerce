<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Generator;

/**
 * Interface GeneratorInterface
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GeneratorInterface
{
    /**
     * Sets the storage.
     *
     * @param string|StorageInterface $storage
     */
    public function setStorage(string|StorageInterface $storage): void;

    /**
     * Generates a new value.
     *
     * @param object $subject
     *
     * @return string
     */
    public function generate(object $subject): string;
}
