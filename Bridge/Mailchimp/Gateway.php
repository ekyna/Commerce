<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Gateway\AbstractGateway;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;

/**
 * Class Gateway
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Gateway extends AbstractGateway
{
    /**
     * @var Api
     */
    private $api;


    /**
     * Constructor.
     *
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function insertAudience(AudienceInterface $audience): void
    {
        throw new NewsletterException("Creating audience through MailChimp API is not supported");
    }

    /**
     * @inheritDoc
     */
    public function updateAudience(AudienceInterface $audience, array $changeSet): void
    {
        $this->checkAudience($audience);

        $map = $this->buildPatchMap($changeSet, [
            'name' => null,
        ]);

        if (empty($map)) {
            return;
        }

        $result = $this->api->patch("lists/{$audience->getIdentifier()}", $map);

        if ($this->api->success()) {
            return;
        }

        $this->api->logError($result);

        throw new NewsletterException(
            "Failed to update audience throught MailChimp API ({$this->api->getLastError()})"
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteAudience(AudienceInterface $audience): void
    {
        throw new NewsletterException("Deleting audience through MailChimp API is not supported");
    }

    /**
     * @inheritDoc
     */
    public function createSubscription(SubscriptionInterface $subscription, object $source = null): void
    {
        $member = $subscription->getMember();
        if (!$source && $member->getCustomer()) {
            $source = $member->getCustomer();
        }

        $attributes = $subscription->getAttributes();

        if (empty($member->getEmail())) {
            $member->setEmail($source->getEmail());
        }

        if (!isset($attributes['FNAME']) && !empty($firsName = $source->getFirstName())) {
            $attributes['FNAME'] = $firsName;
        }

        if (!isset($attributes['LNAME']) && !empty($lastName = $source->getLastName())) {
            $attributes['LNAME'] = $lastName;
        }

        if (!isset($attributes['BIRTHDAY']) && null !== $birthday = $source->getBirthday()) {
            $attributes['BIRTHDAY'] = $birthday->format('m/d');
        }

        $subscription->setAttributes($attributes);
    }

    /**
     * @inheritDoc
     */
    public function insertSubscription(SubscriptionInterface $subscription): void
    {
        $audience = $subscription->getAudience();
        if (empty($audience->getIdentifier())) {
            throw new NewsletterException("Create list first.");
        }

        $member     = $subscription->getMember();
        $audienceId = $audience->getIdentifier();

        $data = [
            'email_address' => $member->getEmail(),
            'status'        => $subscription->getStatus(),
        ];

        if (!empty($attributes = $subscription->getAttributes())) {
            $data['merge_fields'] = $attributes;
        }

        $result = $this->api->post("lists/$audienceId/members", $data);

        if ($this->api->success()) {
            $subscription->setIdentifier($result['web_id']);

            return;
        }

        $this->api->logError($result);

        throw new NewsletterException(
            'Failed to create member through MailChimp API (' . $this->api->getLastError() . ')'
        );
    }

    /**
     * @inheritDoc
     */
    public function updateSubscription(
        SubscriptionInterface $subscription,
        array $subscriptionChanges,
        array $memberChanges
    ): void {
        $this->checkSubscription($subscription);

        $member = $subscription->getMember();
        $email = $member->getEmail();

        $map = $this->buildPatchMap($subscriptionChanges, [
            'status'     => null,
            'attributes' => 'merge_fields',
        ]);

        // Changing email is not allowed
        if (isset($changeSet['email'])) {
            // Restore previous
            $email = $changeSet['email'][0];
            $map['emailAddress'] = $member->getEmail();
        }

        if (empty($map)) {
            return;
        }

        $audienceId = $subscription->getAudience()->getIdentifier();
        $hash       = $this->api->subscriberHash($email);

        $result = $this->api->patch("lists/$audienceId/members/$hash", $map);

        if ($this->api->success()) {
            return;
        }

        $this->api->logError($result);

        throw new NewsletterException(
            'Failed to update member through MailChimp API (' . $this->api->getLastError() . ')'
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteSubscription(SubscriptionInterface $subscription): void
    {
        $this->checkSubscription($subscription);

        $member     = $subscription->getMember();
        $audienceId = $subscription->getAudience()->getIdentifier();
        $hash       = $this->api->subscriberHash($member->getEmail());

        $result = $this->api->delete("lists/$audienceId/members/$hash");

        if ($this->api->success()) {
            return;
        }

        $this->api->logError($result);

        throw new NewsletterException('Failed to delete member (' . $this->api->getLastError() . ')');
    }

    /**
     * @inheritDoc
     */
    public function supports(string $action): bool
    {
        return in_array($action, [
            self::UPDATE_AUDIENCE,
            self::CREATE_SUBSCRIPTION,
            self::INSERT_SUBSCRIPTION,
            self::UPDATE_SUBSCRIPTION,
            self::DELETE_SUBSCRIPTION,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return Constants::NAME;
    }

    /**
     * Builds the patch map.
     *
     * @param array $changeSet
     * @param array $properties
     *
     * @return array
     */
    protected function buildPatchMap(array $changeSet, array $properties): array
    {
        $map = [];

        foreach ($properties as $property => $field) {
            $field = $field ?? $property;
            if (isset($changeSet[$property])) {
                $map[$field] = $changeSet[$property][1];
            }
        }

        return $map;
    }
}
