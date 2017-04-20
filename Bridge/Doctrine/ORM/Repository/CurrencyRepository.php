<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Event\OnClearEventArgs;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class CurrencyRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyRepository extends ResourceRepository implements CurrencyRepositoryInterface
{
    private string $defaultCode;
    private ?array $enabledCodes = null;
    private ?array $allCodes = null;
    private ?CurrencyInterface $defaultCurrency = null;
    private array $cache = [];


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
    public function findDefault(): CurrencyInterface
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
     * @inheritDoc
     */
    public function findOneByCode(string $code): ?CurrencyInterface
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
     * On clear event handler.
     *
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event): void
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultCurrency = null;
            $this->cache = [];
        }
    }
}
