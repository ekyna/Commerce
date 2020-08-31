<?php

namespace Ekyna\Component\Commerce\Newsletter\Gateway;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;

/**
 * Interface GatewayInterface
 * @package Ekyna\Component\Commerce\Newsletter\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface GatewayInterface
{
    public const INSERT_AUDIENCE     = 'insert_audience';
    public const UPDATE_AUDIENCE     = 'update_audience';
    public const DELETE_AUDIENCE     = 'delete_audience';
    public const CREATE_SUBSCRIPTION = 'create_subscription';
    public const INSERT_SUBSCRIPTION = 'insert_subscription';
    public const UPDATE_SUBSCRIPTION = 'update_subscription';
    public const DELETE_SUBSCRIPTION = 'delete_subscription';


    /**
     * Inserts the given audience.
     *
     * @param AudienceInterface $audience
     */
    public function insertAudience(AudienceInterface $audience): void;

    /**
     * Updates the given audience.
     *
     * @param AudienceInterface $audience
     * @param array             $changeSet
     */
    public function updateAudience(AudienceInterface $audience, array $changeSet): void;

    /**
     * Deletes the given audience.
     *
     * @param AudienceInterface $audience
     */
    public function deleteAudience(AudienceInterface $audience): void;

    /**
     * Creates the given subscription.
     *
     * @param SubscriptionInterface $subscription
     * @param object|null           $source
     */
    public function createSubscription(SubscriptionInterface $subscription, object $source = null): void;

    /**
     * Inserts the given subscription.
     *
     * @param SubscriptionInterface $subscription
     */
    public function insertSubscription(SubscriptionInterface $subscription): void;

    /**
     * Updates the given subscription.
     *
     * @param SubscriptionInterface $subscription
     * @param array                 $subscriptionChanges
     * @param array                 $memberChanges
     */
    public function updateSubscription(
        SubscriptionInterface $subscription,
        array $subscriptionChanges,
        array $memberChanges
    ): void;

    /**
     * Deletes the given subscription.
     *
     * @param SubscriptionInterface $subscription
     */
    public function deleteSubscription(SubscriptionInterface $subscription): void;

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
