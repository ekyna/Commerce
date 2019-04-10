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
     * Sets the default code.
     *
     * @param string $code
     */
    public function setDefaultCode($code)
    {
        $this->defaultCode = strtoupper($code);
    }

    /**
     * Sets the cached codes.
     *
     * @param array $codes
     */
    public function setCachedCodes($codes)
    {
        $this->cachedCodes = $codes;
    }

    /**
     * @inheritdoc
     */
    public function findDefault()
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
     * @inheritdoc
     */
    public function findOneByCode($code)
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
     * @inheritdoc
     */
    public function findEnabledCodes()
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
    public function getIdentifiers($cached = false)
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
    public function onClear(OnClearEventArgs $event)
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultCountry = null;
            $this->cache = [];
        }
    }
}
