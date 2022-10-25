<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Report\Fetcher\FetcherInterface;
use Ekyna\Component\Commerce\Report\Section\SectionInterface;
use Ekyna\Component\Commerce\Report\Writer\WriterInterface;

use function array_key_exists;
use function array_keys;

/**
 * Class ReportRegistry
 * @package Ekyna\Component\Commerce\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportRegistry
{
    /**
     * @var array<string, FetcherInterface>
     */
    private array $fetchers = [];

    /**
     * @var array<string, SectionInterface>
     */
    private array $sections = [];

    /**
     * @var array<string, WriterInterface>
     */
    private array $writers = [];

    public function registerFetcher(FetcherInterface $fetcher): void
    {
        if (array_key_exists($resource = $fetcher->provides(), $this->fetchers)) {
            throw new RuntimeException("A fetcher is already registered for resource $resource.");
        }

        $this->fetchers[$resource] = $fetcher;
    }

    /**
     * @return array<string, FetcherInterface>
     */
    public function getFetchers(): array
    {
        return $this->fetchers;
    }

    /**
     * Finds the fetcher by provided resource.
     */
    public function findFetcherByResource(string $resource): FetcherInterface
    {
        foreach ($this->fetchers as $fetcher) {
            if ($resource === $fetcher->provides()) {
                return $fetcher;
            }
        }

        throw new InvalidArgumentException("No registered fetcher provides resource $resource.");
    }

    public function registerSection(SectionInterface $section): void
    {
        if (array_key_exists($name = $section->getName(), $this->sections)) {
            throw new RuntimeException("Section '$name' is already registered.");
        }

        $this->sections[$name] = $section;
    }

    /**
     * @return array<string, SectionInterface>
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @return array<int, string>
     */
    public function getSectionNames(): array
    {
        return array_keys($this->sections);
    }

    /**
     * Finds the section by its name.
     */
    public function findSectionByName(string $name): SectionInterface
    {
        if (array_key_exists($name, $this->sections)) {
            return $this->sections[$name];
        }

        throw new InvalidArgumentException("No section registered for name '$name.");
    }

    public function registerWriter(WriterInterface $writer): void
    {
        if (array_key_exists($name = $writer->getName(), $this->writers)) {
            throw new RuntimeException("Writer '$name' is already registered.");
        }

        $this->writers[$name] = $writer;
    }

    /**
     * @return array<string, WriterInterface>
     */
    public function getWriters(): array
    {
        return $this->writers;
    }

    /**
     * Finds the writer by its name.
     */
    public function findWriterByName(string $name): WriterInterface
    {
        if (array_key_exists($name, $this->writers)) {
            return $this->writers[$name];
        }

        throw new InvalidArgumentException("No writer registered for name '$name.");
    }
}
