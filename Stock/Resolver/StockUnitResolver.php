<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
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
    protected $unitCache;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var array [class => $repository]
     */
    protected $repositoryCache;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface     $subjectHelper
     * @param StockUnitCacheInterface    $unitCache
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        StockUnitCacheInterface $unitCache,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->unitCache = $unitCache;
        $this->persistenceHelper = $persistenceHelper;

        $this->repositoryCache = [];
    }

    /**
     * @inheritdoc
     */
    public function getStockUnitCache()
    {
        return $this->unitCache;
    }

    /**
     * @inheritdoc
     */
    public function createBySubject(StockSubjectInterface $subject)
    {
        // TODO Cache 'new' stock units created by sales
        if (!empty($stockUnits = $this->unitCache->findNewBySubject($subject))) {
            return reset($stockUnits);
        }

        /** @var StockUnitInterface $stockUnit */
        $stockUnit = $this
            ->getRepositoryBySubject($subject)
            ->createNew();

        $stockUnit->setSubject($subject);

        $this->unitCache->add($stockUnit);

        return $stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative)
    {
        /** @var StockSubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($relative);

        return $this->createBySubject($subject);
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
            $this->unitCache->findPendingBySubject($subject),
            $repository->findPendingBySubject($subject)
        );
    }

    /**
     * @inheritdoc
     */
    public function findReady($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return $this->merge(
            $this->unitCache->findReadyBySubject($subject),
            $repository->findReadyBySubject($subject)
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
            $this->unitCache->findPendingOrReadyBySubject($subject),
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
            $this->unitCache->findNotClosedBySubject($subject),
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
            $this->unitCache->findAssignableBySubject($subject),
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
            $this->unitCache->findLinkableBySubject($subject),
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
        foreach ($cachedUnits as $unit) {
            if (null !== $id = $unit->getId()) {
                $cachedIds[] = $unit->getId();
            }
        }

        foreach ($fetchedUnits as $unit) {
            if (in_array($unit->getId(), $cachedIds)) {
                continue;
            }

            if ($this->unitCache->isRemoved($unit)) {
                continue;
            }

            if ($this->persistenceHelper->isScheduledForRemove($unit)) {
                continue;
            }

            $cachedUnits[] = $unit;
        }

        return $cachedUnits;
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

        $repository = $this->persistenceHelper->getManager()->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new InvalidArgumentException('Expected instance of ' . StockUnitRepositoryInterface::class);
        }

        return $this->repositoryCache[$class] = $repository;
    }
}
