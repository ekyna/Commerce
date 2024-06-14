<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

/**
 * Class StockUnitHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitHelper
{
    public function __construct(
        private readonly RepositoryFactoryInterface $repositoryFactory,
    ) {
    }

    public function getRepository(StockSubjectInterface $subject): StockUnitRepositoryInterface
    {
        $repository = $this->repositoryFactory->getRepository($subject::getStockUnitClass());

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StockUnitRepositoryInterface::class);
        }

        return $repository;
    }
}
