<?php

declare(strict_types=1);

namespace Acme\Product\Repository;

use Acme\Product\Entity\Product;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Repository\AbstractStockUnitRepository;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

/**
 * Class StockUnitRepository
 * @package Acme\Product\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitRepository extends AbstractStockUnitRepository
{
    protected function assertSubject(StockSubjectInterface $subject): void
    {
        if ($subject instanceof Product) {
            return;
        }

        throw new UnexpectedTypeException($subject, Product::class);
    }

    /**
     * @inheritDoc
     */
    protected function getAlias(): string
    {
        return 'su';
    }
}
