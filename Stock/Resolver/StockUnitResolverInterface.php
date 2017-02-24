<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface StockUnitResolverInterface
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitResolverInterface
{
    /**
     * Creates (and initializes) a stock unit for the given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createBySupplierOrderItem(SupplierOrderItemInterface $item);

    /**
     * Returns the relative subject provider.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface|null
     *
     * @deprecated
     */
    public function getProviderByRelative(SubjectRelativeInterface $relative);

    /**
     * Returns the stock unit repository by subject relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return StockUnitRepositoryInterface
     */
    public function getRepositoryByRelative(SubjectRelativeInterface $relative);

    /**
     * Returns the stock unit repository by subject.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitRepositoryInterface
     */
    public function getRepositoryBySubject(StockSubjectInterface $subject);
}
