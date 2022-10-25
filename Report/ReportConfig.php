<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report;

use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Ekyna\Component\Resource\Model\DateRange;

use function array_search;
use function in_array;

/**
 * Class ReportConfig
 * @package Ekyna\Component\Commerce\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportConfig
{
    public DateRange $range;
    public string    $writer   = XlsWriter::NAME;
    public string    $locale   = 'en';
    public ?string   $email    = null;
    public bool      $test     = false;
    private array    $sections = [];

    public function __construct()
    {
        $this->range = new DateRange();
    }

    public function addSection(string $section): void
    {
        if (in_array($section, $this->sections, true)) {
            return;
        }

        $this->sections[] = $section;
    }

    public function removeSection(string $section): void
    {
        if (false === $index = array_search($section, $this->sections, true)) {
            return;
        }

        unset($this->sections[$index]);
    }

    /**
     * @return array<int, string>
     */
    public function getSections(): array
    {
        return $this->sections;
    }
}
