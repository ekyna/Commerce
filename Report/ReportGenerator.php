<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report;

use Ekyna\Component\Commerce\Report\Section\SectionInterface;
use Psr\Log\LoggerInterface;

use function array_filter;
use function array_key_exists;
use function in_array;

/**
 * Class ReportGenerator
 * @package Ekyna\Component\Commerce\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportGenerator
{
    public function __construct(
        private readonly ReportRegistry $registry
    ) {
    }

    public function generate(ReportConfig $config, LoggerInterface $logger = null): string
    {
        $sections = [];
        foreach ($config->getSections() as $name) {
            $sections[] = $this->registry->findSectionByName($name);
        }

        $writer = $this->registry->findWriterByName($config->writer);

        // Select needed fetchers
        $fetchers = [];
        foreach ($sections as $section) {
            $resources = $section->requiresResources();
            foreach ($resources as $resource) {
                if (array_key_exists($resource, $fetchers)) {
                    continue;
                }

                $fetchers[$resource] = $this->registry->findFetcherByResource($resource);
            }
        }

        // Initializes the fetchers
        foreach ($fetchers as $fetcher) {
            $fetcher->initialize($config);
        }

        // Initializes the sections
        foreach ($sections as $section) {
            $section->initialize($config);
        }

        $size = $config->test ? 5 : 30;

        // Make each section read each fetched resource
        foreach ($fetchers as $fetcher) {
            $selection = array_filter(
                $sections,
                fn(SectionInterface $s): bool => in_array($fetcher->provides(), $s->requiresResources(), true)
            );

            foreach ($config->range->byMonths() as $month) {
                $logger?->debug('Month ' . $month->getStart()->format('Y-m'));

                $page = 0;
                while (!empty($resources = $fetcher->fetch($month, $page, $size))) {
                    foreach ($resources as $resource) {
                        $logger?->debug((string)$resource);

                        foreach ($selection as $section) {
                            $section->read($resource);
                        }
                    }

                    if ($config->test) {
                        break;
                    }

                    $page++;
                }
            }
        }

        $writer->initialize();

        // Make each section write report
        foreach ($sections as $section) {
            $section->write($writer);
        }

        return $writer->terminate();
    }
}
