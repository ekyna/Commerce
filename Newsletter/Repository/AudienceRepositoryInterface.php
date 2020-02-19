<?php

namespace Ekyna\Component\Commerce\Newsletter\Repository;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface AudienceRepositoryInterface
 * @package Ekyna\Component\Commerce\Newsletter\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AudienceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the default audience.
     *
     * @return AudienceInterface
     */
    public function findDefault(): AudienceInterface;

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
     * Finds one audience by its identifier.
     *
     * @param string $gateway
     * @param string $identifier
     *
     * @return AudienceInterface|null
     */
    public function findOneByGatewayAndIdentifier(string $gateway, string $identifier): ?AudienceInterface;

    /**
     * Finds audiences by gateway with webhook not configured.
     *
     * @param string $gateway
     *
     * @return AudienceInterface[]
     */
    public function findByGatewayWithWebhookNotConfigured(string $gateway): array;

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
