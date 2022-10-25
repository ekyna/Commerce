<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Section;

use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Interface SectionInterface
 * @package Ekyna\Component\Commerce\Report\Section
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SectionInterface
{
    /**
     * Initializes the section.
     */
    public function initialize(ReportConfig $config): void;

    /**
     * Calculates and gather data for the given resource.
     */
    public function read(ResourceInterface $resource): void;

    /**
     * Writes the report through the given writer.
     */
    public function write(WriterInterface $writer): void;

    /**
     * Returns the required resource's classes.
     *
     * @return array<int, string>
     */
    public function requiresResources(): array;

    /**
     * Returns whether the given writer is supported.
     */
    public function supportsWriter(string $writerClass): bool;

    /**
     * Returns the section name.
     */
    public function getName(): string;

    /**
     * Returns the section title.
     */
    public function getTitle(): TranslatableInterface;
}
