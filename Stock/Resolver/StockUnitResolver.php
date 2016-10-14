<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Class StockUnitResolver
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitResolver implements StockUnitResolverInterface
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    protected $subjectProviderRegistry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $subjectProviderRegistry
     */
    public function __construct(SubjectProviderRegistryInterface $subjectProviderRegistry)
    {
        $this->subjectProviderRegistry = $subjectProviderRegistry;
    }

    /**
     * Returns the relative subject provider.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface|null
     */
    public function getProviderByRelative(SubjectRelativeInterface $relative)
    {
        return $this
            ->subjectProviderRegistry
            ->getProviderByRelative($relative);
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryBySubject(StockSubjectInterface $subject)
    {
        $provider = $this
            ->subjectProviderRegistry
            ->getProviderBySubject($subject);

        if (null !== $provider) {
            return $provider->getStockUnitRepository();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function resolveBySubject(StockSubjectInterface $subject)
    {
        $provider = $this
            ->subjectProviderRegistry
            ->getProviderBySubject($subject);

        if (null !== $provider) {
            return $provider
                ->getStockUnitRepository()
                ->findAvailableOrPendingStockUnitsBySubject($subject);
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function resolveBySupplierOrderItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        $provider = $this
            ->subjectProviderRegistry
            ->getProviderByRelative($supplierOrderItem);

        if (null !== $provider) {
            $repository = $provider->getStockUnitRepository();
            if (null !== $repository) {
                return $repository->findOneBySupplierOrderItem($supplierOrderItem);
            }
        }

        return null;
    }
}
