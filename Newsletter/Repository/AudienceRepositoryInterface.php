<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Repository;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Cache\ResultCacheAwareInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface AudienceRepositoryInterface
 * @package Ekyna\Component\Commerce\Newsletter\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AudienceRepositoryInterface extends TranslatableRepositoryInterface, ResultCacheAwareInterface
{
    public const DEFAULT_CACHE_KEY = 'commerce_newsletter_default';

    /**
     * Returns the default audience.
     *
     * @return AudienceInterface
     *
     * @throws CommerceExceptionInterface
     */
    public function findDefault(): AudienceInterface;

    /**
     * Purges the default audience.
     */
    public function purgeDefault(): void;

    /**
     * Returns the "find public" query builder.
     *
     * @return QueryBuilder
     */
    public function getFindPublicQueryBuilder(): QueryBuilder;

    /**
     * Returns the public audiences.
     *
     * @return AudienceInterface[]
     */
    public function findPublic(): array;

    /**
     * Finds one audience by its key.
     *
     * @param string $key
     *
     * @return AudienceInterface|null
     */
    public function findOneByKey(string $key): ?AudienceInterface;

    /**
     * Finds audiences by gateway.
     *
     * @param string $gateway
     *
     * @return AudienceInterface[]
     */
    public function findByGateway(string $gateway): array;

    /**
     * Finds one audience by its identifier.
     *
     * @param string $gateway
     * @param string $identifier
     *
     * @return AudienceInterface|null
     */
    public function findOneByGatewayAndIdentifier(string $gateway, string $identifier): ?AudienceInterface;

    /**
     * Finds audiences by gateway excluding given identifiers.
     *
     * @param string $gateway
     * @param array  $identifiers
     *
     * @return AudienceInterface[]
     */
    public function findByGatewayExcludingIds(string $gateway, array $identifiers): array;
}
