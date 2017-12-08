<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitResolver
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitResolver implements StockUnitResolverInterface
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var StockUnitCacheInterface
     */
    protected $stockUnitCache;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array [class => $repository]
     */
    protected $repositoryCache;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface     $subjectHelper
     * @param StockUnitCacheInterface    $stockUnitCache
     * @param PersistenceHelperInterface $persistenceHelper
     * @param EntityManagerInterface     $entityManager
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        StockUnitCacheInterface $stockUnitCache,
        PersistenceHelperInterface $persistenceHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->stockUnitCache = $stockUnitCache;
        $this->persistenceHelper = $persistenceHelper;
        $this->entityManager = $entityManager;

        $this->repositoryCache = [];
    }

    /**
     * @inheritdoc
     */
    public function getStockUnitCache()
    {
        return $this->stockUnitCache;
    }

    /**
     * @inheritdoc
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative)
    {
        /** @var StockSubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($relative);

        // TODO Cache 'new' stock units created by sales
        if (!empty($stockUnits = $this->stockUnitCache->findNewBySubject($subject))) {
            return reset($stockUnits);
        }

        /** @var StockUnitInterface $stockUnit */
        $stockUnit = $this
            ->getRepositoryBySubject($subject)
            ->createNew();

        $stockUnit->setSubject($subject);

        $this->stockUnitCache->add($stockUnit);

        return $stockUnit;
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    public function createBySupplierOrderItem(SupplierOrderItemInterface $item)
    {
        return $this
            ->createBySubjectRelative($item)
            ->setSupplierOrderItem($item)
            ->setNetPrice($item->getNetPrice())
            ->setOrderedQuantity($item->getQuantity())
            ->setEstimatedDateOfArrival($item->getOrder()->getEstimatedDateOfArrival());
    }

    /**
     * @inheritdoc
     */
    public function findPending($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return $this->merge(
            $this->stockUnitCache->findPendingBySubject($subject),
            $repository->findPendingBySubject($subject)
        );
    }

    /**
     * @inheritdoc
     */
    public function findPendingOrReady($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return $this->merge(
            $this->stockUnitCache->findPendingOrReadyBySubject($subject),
            $repository->findPendingOrReadyBySubject($subject)
        );
    }

    /**
     * @inheritdoc
     */
    public function findNotClosed($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        $stockUnits = $this->merge(
            $this->stockUnitCache->findNotClosedBySubject($subject),
            $repository->findNotClosedBySubject($subject)
        );

        return $stockUnits;
    }

    /**
     * @inheritdoc
     */
    public function findAssignable($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return $this->merge(
            $this->stockUnitCache->findAssignableBySubject($subject),
            $repository->findAssignableBySubject($subject)
        );
    }

    /**
     * @inheritdoc
     */
    public function findLinkable($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        $stockUnits = $this->merge(
            $this->stockUnitCache->findLinkableBySubject($subject),
            $repository->findLinkableBySubject($subject)
        );

        if (!empty($stockUnits)) {
            return reset($stockUnits);
        }

        return null;
    }

    /**
     * Merges the fetched units with the cached units.
     *
     * @param StockUnitInterface[] $cachedUnits
     * @param StockUnitInterface[] $fetchedUnits
     *
     * @return array
     */
    protected function merge(array $cachedUnits, array $fetchedUnits)
    {
        $cachedIds = [];
        foreach ($cachedUnits as $cachedUnit) {
            if (null !== $id = $cachedUnit->getId()) {
                $cachedIds[] = $cachedUnit->getId();
            }
        }

        foreach ($fetchedUnits as $fetchedUnit) {
            if (!in_array($fetchedUnit->getId(), $cachedIds)) {
                $cachedUnits[] = $fetchedUnit;
            }
        }

        return array_filter($cachedUnits, function($stockUnit) {
            return !$this->persistenceHelper->isScheduledForRemove($stockUnit);
        });
    }

    /**
     * Returns the subject and his stock unit repository.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array(StockSubjectInterface, StockUnitRepositoryInterface)
     */
    protected function getSubjectAndRepository($subjectOrRelative)
    {
        if ($subjectOrRelative instanceof SubjectRelativeInterface) {
            $subject = $this->subjectHelper->resolve($subjectOrRelative);
        } elseif ($subjectOrRelative instanceof StockSubjectInterface) {
            $subject = $subjectOrRelative;
        } else {
            throw new InvalidArgumentException(sprintf(
                "Expected instance of '%s' or '%s'.",
                SubjectRelativeInterface::class,
                StockSubjectInterface::class
            ));
        }

        return [$subject, $this->getRepositoryBySubject($subject)];
    }

    /**
     * Returns the subject and his stock unit repository.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitRepositoryInterface
     */
    protected function getRepositoryBySubject(StockSubjectInterface $subject)
    {
        $class = $subject::getStockUnitClass();

        if (isset($this->repositoryCache[$class])) {
            return $this->repositoryCache[$class];
        }

        $repository = $this->entityManager->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new InvalidArgumentException('Expected instance of ' . StockUnitRepositoryInterface::class);
        }

        return $this->repositoryCache[$class] = $repository;
    }
}
