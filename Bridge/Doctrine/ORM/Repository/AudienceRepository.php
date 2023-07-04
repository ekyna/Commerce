<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Cache\ResultCacheAwareTrait;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class AudienceRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceRepository extends TranslatableRepository implements AudienceRepositoryInterface
{
    use ResultCacheAwareTrait;

    private ?AudienceInterface $defaultAudience               = null;
    private ?Query             $findOneByKeyQuery             = null;
    private ?Query             $findOneByGatewayAndIdentifier = null;


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
            ->enableResultCache(60 * 60 * 24, self::DEFAULT_CACHE_KEY)
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

        if (null === $cache = $this->getResultCache()) {
            return;
        }

        $cache = DoctrineProvider::wrap($cache);

        $cache->delete(self::DEFAULT_CACHE_KEY);
    }

    /**
     * Returns the "find public" query builder.
     */
    public function getFindPublicQueryBuilder(): QueryBuilder
    {
        $qb = $this->getQueryBuilder('a');

        return $qb
            ->andWhere($qb->expr()->eq('a.public', ':public'))
            ->addOrderBy('translation.title', 'ASC');
    }

    public function findPublic(): array
    {
        return $this
            ->getFindPublicQueryBuilder()
            ->getQuery()
            ->setParameter('public', true)
            ->getResult();
    }

    public function findOneByKey(string $key): ?AudienceInterface
    {
        return $this
            ->getFindOneByKeyQuery()
            ->setParameter('key', $key)
            ->getOneOrNullResult();
    }

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
     */
    public function onClear()
    {
        $this->defaultAudience = null;
    }

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
