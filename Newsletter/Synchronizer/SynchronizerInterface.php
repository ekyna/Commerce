<?php

namespace Ekyna\Component\Commerce\Newsletter\Synchronizer;

use Psr\Log\LoggerInterface;

/**
 * Interface SynchronizerInterface
 * @package Ekyna\Component\Commerce\Newsletter\Synchronizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SynchronizerInterface
{
    /**
     * Synchronizes the audiences.
     *
     * @param LoggerInterface|null $logger
     */
    public function synchronize(LoggerInterface $logger = null): void;

    /**
     * Returns the synchronizer name.
     *
     * @return string
     */
    public static function getName(): string;
}
