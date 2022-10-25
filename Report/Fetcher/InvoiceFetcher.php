<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Fetcher;

use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\DateRange;

use function gc_collect_cycles;

/**
 * Class InvoiceFetcher
 * @package Ekyna\Component\Commerce\Report\Fetcher
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceFetcher implements FetcherInterface
{
    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly ResourceManagerInterface $manager,
    ) {
    }

    /**
     * @inheritDoc
     */
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

        return $this->repository->findByCreatedAt($range, $page, $size);
    }

    /**
     * @inheritDoc
     */
    public function provides(): string
    {
        return OrderInvoiceInterface::class;
    }
}
