<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Fetcher;

use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface FetcherInterface
 * @package Ekyna\Component\Commerce\Report\Fetcher
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface FetcherInterface
{
    /**
     * Initializes the fetcher.
     */
    public function initialize(ReportConfig $config): void;

    /**
     * Fetches the resources.
     *
     * @return array<int, ResourceInterface>
     */
    public function fetch(DateRange $range, int $page, int $size): array;

    /**
     * The resource's class this fetcher fetches.
     */
    public function provides(): string;
}
