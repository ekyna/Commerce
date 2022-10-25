<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Fetcher;

use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\DateRange;

use function gc_collect_cycles;

/**
 * Class SupplierOrderFetcher
 * @package Ekyna\Component\Commerce\Report\Fetcher
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderFetcher implements FetcherInterface
{
    public function __construct(
        private readonly SupplierOrderRepositoryInterface $repository,
        private readonly ResourceManagerInterface         $manager
    ) {
    }

    public function initialize(ReportConfig $config): void
    {
    }

    /**
     * @inheritDoc
     */
    public function fetch(DateRange $range, int $page, int $size): array
    {
        $this->manager->clear();
        gc_collect_cycles();

        return $this->repository->findByOrderAt($range, $page, $size);
    }

    /**
     * @inheritDoc
     */
    public function provides(): string
    {
        return SupplierOrderInterface::class;
    }
}
