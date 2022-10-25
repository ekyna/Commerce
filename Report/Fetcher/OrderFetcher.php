<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Report\Fetcher;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\Util\OrderUtil;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\DateRange;

use function gc_collect_cycles;

/**
 * Class OrderFetcher
 * @package Ekyna\Component\Commerce\Report\Fetcher
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderFetcher implements FetcherInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly ResourceManagerInterface $manager,
        private readonly OrderUtil $marginUtil
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

        $this->marginUtil->clear();

        return $this->repository->findByAcceptedAt($range, $page, $size);
    }

    /**
     * @inheritDoc
     */
    public function provides(): string
    {
        return OrderInterface::class;
    }
}
