<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Event\OnClearEventArgs;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CurrencyRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyRepository extends ResourceRepository implements CurrencyRepositoryInterface
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
     * @var CurrencyInterface
     */
    private $defaultCurrency;

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
     * @inheritdoc
     */
    public function findDefault()
    {
        if (null !== $this->defaultCurrency) {
            return $this->defaultCurrency;
        }

        if (null !== $currency = $this->findOneByCode($this->defaultCode)) {
            return $this->defaultCurrency = $currency;
        }

        throw new RuntimeException('Default currency not found.');
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
            ->setParameter('code', strtoupper($code))
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
     * @inheritdoc
     */
    public function findAllCodes()
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
     * On clear event handler.
     *
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event)
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultCurrency = null;
            $this->cache = [];
        }
    }
}
