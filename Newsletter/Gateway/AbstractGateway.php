<?php

namespace Ekyna\Component\Commerce\Newsletter\Gateway;

use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Component\Commerce\Newsletter\Gateway
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway implements GatewayInterface
{
    /**
     * @inheritDoc
     */
    public function insertAudience(AudienceInterface $audience): void
    {

    }

    /**
     * @inheritDoc
     */
    public function updateAudience(AudienceInterface $audience, array $changeSet): void
    {

    }

    /**
     * @inheritDoc
     */
    public function deleteAudience(AudienceInterface $audience): void
    {

    }

    /**
     * @inheritDoc
     */
    public function createSubscription(SubscriptionInterface $subscription, object $source = null): void
    {

    }

    /**
     * @inheritDoc
     */
    public function insertSubscription(SubscriptionInterface $subscription): void
    {

    }

    /**
     * @inheritDoc
     */
    public function updateSubscription(
        SubscriptionInterface $subscription,
        array $subscriptionChanges,
        array $memberChanges
    ): void {

    }

    /**
     * @inheritDoc
     */
    public function deleteSubscription(SubscriptionInterface $subscription): void
    {

    }

    /**
     * @inheritDoc
     */
    public function supports(string $action): bool
    {
        return false;
    }

    /**
     * Checks the audience.
     *
     * @param AudienceInterface $audience
     *
     * @throws NewsletterException
     */
    protected function checkAudience(AudienceInterface $audience): void
    {
        if ($audience->getGateway() === static::getName()) {
            return;
        }

        if (!empty($audience->getIdentifier())) {
            return;
        }

        throw new NewsletterException("Unexpected audience (wrong gateway / missing identifier)");
    }

    /**
     * Checks the member.
     *
     * @param MemberInterface $member
     *
     * @throws NewsletterException
     */
    protected function checkMember(MemberInterface $member): void
    {
        if ($member->hasIdentifier(static::getName())) {
            return;
        }

        throw new NewsletterException("Unexpected member (missing identifier)");
    }

    /**
     * Checks the subscription.
     *
     * @param SubscriptionInterface $subscription
     *
     * @throws NewsletterException
     */
    protected function checkSubscription(SubscriptionInterface $subscription): void
    {
        if (!empty($subscription->getIdentifier())) {
            return;
        }

        throw new NewsletterException("Unexpected subscription (missing identifier)");
    }
}
