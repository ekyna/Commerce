<?php

namespace Ekyna\Component\Commerce\Newsletter\Gateway;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;

/**
 * Interface GatewayInterface
 * @package Ekyna\Component\Commerce\Newsletter\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface GatewayInterface
{
    // Actions
    public const CREATE_AUDIENCE   = 'create audience';
    public const UPDATE_AUDIENCE   = 'update audience';
    public const DELETE_AUDIENCE   = 'delete audience';
    public const CREATE_MEMBER     = 'create member';
    public const UPDATE_MEMBER     = 'update member';
    public const DELETE_MEMBER     = 'delete member';


    /**
     * Creates the given audience.
     *
     * @param AudienceInterface $audience
     *
     * @return bool
     */
    public function createAudience(AudienceInterface $audience): bool;

    /**
     * Updates the given audience.
     *
     * @param AudienceInterface $audience
     * @param array             $changeSet
     *
     * @return bool
     */
    public function updateAudience(AudienceInterface $audience, array $changeSet): bool;

    /**
     * Deletes the given audience.
     *
     * @param AudienceInterface $audience
     *
     * @return bool
     */
    public function deleteAudience(AudienceInterface $audience): bool;

    /**
     * Creates the given member.
     *
     * @param MemberInterface $member
     *
     * @return bool
     */
    public function createMember(MemberInterface $member): bool;

    /**
     * Updates the given member.
     *
     * @param MemberInterface $member
     * @param array           $changeSet
     *
     * @return bool
     */
    public function updateMember(MemberInterface $member, array $changeSet): bool;

    /**
     * Deletes the given member.
     *
     * @param MemberInterface $member
     *
     * @return bool
     */
    public function deleteMember(MemberInterface $member): bool;

    /**
     * Returns whether this gateway supports the given action.
     *
     * @param string $action
     *
     * @return bool
     */
    public function supports(string $action): bool;

    /**
     * Returns the gateway name.
     *
     * @return string
     */
    public static function getName(): string;
}
