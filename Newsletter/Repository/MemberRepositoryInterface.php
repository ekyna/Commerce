<?php

namespace Ekyna\Component\Commerce\Newsletter\Repository;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface MemberRepositoryInterface
 * @package Ekyna\Component\Commerce\Newsletter\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface MemberRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds one member by gateway and identifier.
     *
     * @param string $gateway
     * @param string $identifier
     *
     * @return MemberInterface|null
     */
    public function findOneByGatewayAndIdentifier(string $gateway, string $identifier): ?MemberInterface;

    /**
     * Finds the member by gateway and email address.
     *
     * @param string $gateway
     * @param string $email
     *
     * @return MemberInterface|null
     */
    public function findOneByGatewayAndEmail(string $gateway, string $email): ?MemberInterface;

    /**
     * Finds the member by audience and email address.
     *
     * @param AudienceInterface $audience
     * @param string            $email
     *
     * @return MemberInterface|null
     */
    public function findOneByAudienceAndEmail(AudienceInterface $audience, string $email): ?MemberInterface;

    /**
     * Finds members having an identifier not in the given ones.
     *
     * @param string $gateway
     * @param array  $identifiers
     *
     * @return MemberInterface[]
     */
    public function findByGatewayAndExcludingIds(string $gateway, array $identifiers): array;
}
