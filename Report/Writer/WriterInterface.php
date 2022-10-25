<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Writer;

use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Interface WriterInterface
 * @package Ekyna\Component\Commerce\Report\Writer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface WriterInterface
{
    /**
     * Initializes the writing process.
     */
    public function initialize(): void;

    /**
     * Terminates the writing process.
     */
    public function terminate(): string;

    /**
     * Returns the writer name.
     */
    public function getName(): string;

    /**
     * Returns the writer title.
     */
    public function getTitle(): TranslatableInterface;
}
