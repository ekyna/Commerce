<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Event\OnClearEventArgs;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CountryRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryRepository extends ResourceRepository implements CountryRepositoryInterface
{
    /**
     * @var string
     */
    private $defaultCode;

    /**
     * @var string[]
     */
    private $enabledCodes;

    /**
     * @var string[]
     */
    private $allCodes;

    /**
     * @var CountryInterface
     */
    private $defaultCountry;

    /**
     * @var array
     */
    private $cachedCodes;

    /**
     * @var array
     */
    private $cache = [];


    /**
     * @inheritDoc
     */
    public function getDefaultCode(): string
    {
        return $this->defaultCode;
    }

    /**
     * @inheritDoc
     */
    public function setDefaultCode(string $code): void
    {
        $this->defaultCode = strtoupper($code);
    }

    /**
     * @inheritDoc
     */
    public function setCachedCodes(array $codes): void
    {
        $this->cachedCodes = $codes;
    }

    /**
     * @inheritDoc
     */
    public function findDefault(): CountryInterface
    {
        if (null !== $this->defaultCountry) {
            return $this->defaultCountry;
        }

        if (null !== $country = $this->findOneByCode($this->defaultCode)) {
            return $this->defaultCountry = $country;
        }

        throw new RuntimeException('Default country not found.');
    }

    /**
     * @inheritDoc
     */
    public function findOneByCode(string $code): ?CountryInterface
    {
        $code = strtoupper($code);

        if (isset($this->cache[$code])) {
            return $this->cache[$code];
        }

        return $this->cache[$code] = $this
            ->getQueryBuilder('c')
            ->andWhere('c.code = :code')
            ->getQuery()
            ->setParameter('code', $code)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findEnabledCodes(): array
    {
        if (null !== $this->enabledCodes) {
            return $this->enabledCodes;
        }

        // TODO Caching

        $result = $this
            ->getQueryBuilder('c')
            ->select('c.code')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getScalarResult();

        return $this->enabledCodes = array_column($result, 'code');
    }

    /**
     * @inheritDoc
     */
    public function findAllCodes(): array
    {
        if (null !== $this->allCodes) {
            return $this->allCodes;
        }

        // TODO Caching

        $result = $this
            ->getQueryBuilder('c')
            ->select('c.code')
            ->getQuery()
            ->getScalarResult();

        return $this->allCodes = array_column($result, 'code');
    }

    /**
     * @inheritDoc
     */
    public function getIdentifiers(bool $cached = false): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('c.id')
            ->orderBy('c.id');

        if ($cached) {
            $qb
                ->andWhere($qb->expr()->in('c.code', ':codes'))
                ->setParameter('codes', $this->cachedCodes);
        }

        return array_column($qb->getQuery()->getScalarResult(), 'id');
    }

    /**
     * On clear event handler.
     *
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event): void
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultCountry = null;
            $this->cache = [];
        }
    }
}
