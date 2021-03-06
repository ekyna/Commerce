<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class AudienceRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceRepository extends TranslatableResourceRepository implements AudienceRepositoryInterface
{
    /**
     * @var AudienceInterface
     */
    private $defaultAudience;

    /**
     * @var Query
     */
    private $findOneByKeyQuery;

    /**
     * @var Query
     */
    private $findOneByGatewayAndIdentifier;


    /**
     * @inheritDoc
     */
    public function findDefault(): AudienceInterface
    {
        if (null !== $this->defaultAudience) {
            return $this->defaultAudience;
        }

        $qb = $this->getQueryBuilder('a');
        $qb
            ->andWhere($qb->expr()->eq('a.default', true))
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(60*60*24, self::DEFAULT_CACHE_KEY)
            ->getOneOrNullResult();

        if (null !== $this->defaultAudience = $this->findOneBy(['default' => true])) {
            return $this->defaultAudience;
        }

        throw new RuntimeException('Default audience not found.');
    }

    /**
     * Purges the default audience.
     */
    public function purgeDefault(): void
    {
        $this->defaultAudience = null;

        $this
            ->getEntityManager()
            ->getConfiguration()
            ->getResultCacheImpl()
            ->delete(self::DEFAULT_CACHE_KEY);
    }

    /**
     * Returns the "find public" query builder.
     *
     * @return QueryBuilder
     */
    public function getFindPublicQueryBuilder(): QueryBuilder
    {
        $qb = $this->getQueryBuilder('a');

        return $qb
            ->andWhere($qb->expr()->eq('a.public', ':public'))
            ->addOrderBy('translation.title', 'ASC');
    }

    /**
     * @inheritDoc
     */
    public function findPublic(): array
    {
        return $this
            ->getFindPublicQueryBuilder()
            ->getQuery()
            ->setParameter('public', true)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByKey(string $key): ?AudienceInterface
    {
        return $this
            ->getFindOneByKeyQuery()
            ->setParameter('key', $key)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByGateway(string $gateway): array
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->andWhere($qb->expr()->eq('a.gateway', ':gateway'))
            ->andWhere($qb->expr()->isNotNull('a.identifier'))
            ->getQuery()
            ->setParameters([
                'gateway' => $gateway,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByGatewayAndIdentifier(string $gateway, string $identifier): ?AudienceInterface
    {
        return $this
            ->getFindOneByGatewayAndIdentifierQuery()
            ->setParameters([
                'gateway'    => $gateway,
                'identifier' => $identifier,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByGatewayExcludingIds(string $gateway, array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a');

        return $qb
            ->andWhere($qb->expr()->eq('a.gateway', ':gateway'))
            ->andWhere($qb->expr()->notIn('a.identifier', ':identifiers'))
            ->getQuery()
            ->setParameters([
                'gateway'     => $gateway,
                'identifiers' => $identifiers,
            ])
            ->getResult();
    }

    /**
     * On clear event handler.
     *
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event)
    {
        if ((null === $event->getEntityClass()) || ($this->getClassName() === $event->getEntityClass())) {
            $this->defaultAudience = null;
        }
    }

    /**
     * @return Query
     */
    private function getFindOneByKeyQuery(): Query
    {
        if ($this->findOneByKeyQuery) {
            return $this->findOneByKeyQuery;
        }

        $qb = $this->createQueryBuilder('a');

        return $this->findOneByKeyQuery = $qb
            ->andWhere($qb->expr()->eq('a.key', ':key'))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return Query
     */
    private function getFindOneByGatewayAndIdentifierQuery(): Query
    {
        if ($this->findOneByGatewayAndIdentifier) {
            return $this->findOneByGatewayAndIdentifier;
        }

        $qb = $this->createQueryBuilder('a');

        return $this->findOneByGatewayAndIdentifier = $qb
            ->andWhere($qb->expr()->eq('a.gateway', ':gateway'))
            ->andWhere($qb->expr()->eq('a.identifier', ':identifier'))
            ->getQuery()
            ->useQueryCache(true);
    }
}
