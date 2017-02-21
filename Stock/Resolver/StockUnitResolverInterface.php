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
     * Create stock unit for the given object.
     *
     * @param SubjectRelativeInterface|object|string $object
     *
     * @return StockUnitInterface
     */
    public function createStockUnit($object);

    /**
     * Returns the relative subject provider.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface|null
     */
    public function getProviderByRelative(SubjectRelativeInterface $relative);

    /**
     * Returns the stock unit repository by subject.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitRepositoryInterface|null
     */
    public function getRepositoryBySubject(StockSubjectInterface $subject);

    /**
     * Returns the subject's available or pending stock units.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitInterface[]
     */
    public function resolveBySubject(StockSubjectInterface $subject);

    /**
     * Returns the stock unit attached to the given supplier order item.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @return StockUnitInterface|null
     */
    public function resolveBySupplierOrderItem(SupplierOrderItemInterface $supplierOrderItem);
}
