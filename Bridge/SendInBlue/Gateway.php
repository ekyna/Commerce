<?php

namespace Ekyna\Component\Commerce\Bridge\SendInBlue;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Gateway\AbstractGateway;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\NewsletterSubscription;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;

/**
 * Class Gateway
 * @package Ekyna\Component\Commerce\Bridge\SendInBlue
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
        $id = $this->api->createList($audience->getName());

        if ($id) {
            $audience
                ->setGateway(Constants::NAME)
                ->setIdentifier($id);

            return;
        }

        throw new NewsletterException("Failed to create list through SendInBlue API");
    }

    /**
     * @inheritDoc
     */
    public function updateAudience(AudienceInterface $audience, array $changeSet): void
    {
        $this->checkAudience($audience);

        if (!isset($changeSet['name'])) {
            return;
        }

        if ($this->api->updateList((int)$audience->getIdentifier(), $audience->getName())) {
            return;
        }

        throw new NewsletterException("Failed to update list through SendInBlue API");
    }

    /**
     * @inheritDoc
     */
    public function deleteAudience(AudienceInterface $audience): void
    {
        $this->checkAudience($audience);

        if ($this->api->deleteList((int)$audience->getIdentifier())) {
            return;
        }

        throw new NewsletterException("Failed to delete list through SendInBlue API");
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

        if (!$source instanceof CustomerInterface && !$source instanceof NewsletterSubscription) {
            return;
        }

        if (!isset($attributes['FIRSTNAME']) && !empty($firsName = $source->getFirstName())) {
            $attributes['FIRSTNAME'] = $firsName;
        }

        if (!isset($attributes['LASTNAME']) && !empty($lastName = $source->getLastName())) {
            $attributes['LASTNAME'] = $lastName;
        }

        if (!isset($attributes['BIRTHDAY']) && ($birthday = $source->getBirthday())) {
            $attributes['BIRTHDAY'] = $birthday->format('d/m/d');
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

        $member = $subscription->getMember();

        if (!$member->hasIdentifier(static::getName())) {
            $listIds = [];
            if ($subscription->getStatus() === SubscriptionStatus::SUBSCRIBED) {
                $listIds[] = (int)$audience->getIdentifier();
            }

            $id = $this->api->createContact(
                $member->getEmail(),
                $subscription->getAttributes(),
                $listIds
            );

            if ($id) {
                $member->setIdentifier(static::getName(), $id);

                return;
            }

            throw new NewsletterException("Failed to create contact through SendInBlue API");
        }

        if ($subscription->getStatus() !== SubscriptionStatus::SUBSCRIBED) {
            return;
        }

        if ($this->api->addContactToList((int)$audience->getIdentifier(), $member->getEmail())) {
            return;
        }

        throw new NewsletterException("Failed to add contact to list through SendInBlue API");
    }

    /**
     * @inheritDoc
     */
    public function updateSubscription(
        SubscriptionInterface $subscription,
        array $subscriptionChanges,
        array $memberChanges
    ): void {
        $this->checkMember($subscription->getMember());

        $email = $subscription->getMember()->getEmail();

        if (isset($subscriptionChanges['status'])) {
            $audienceId = $subscription->getAudience()->getIdentifier();

            if ($subscription->getStatus() === SubscriptionStatus::SUBSCRIBED) {
                if (!$this->api->addContactToList((int)$audienceId, $email)) {
                    throw new NewsletterException("Failed to add contact to list through SendInBlue API");
                }
            } elseif (!$this->api->removeContactFromList((int)$audienceId, $email)) {
                throw new NewsletterException("Failed to remove contact from list through SendInBlue API");
            }

            if (1 === count($subscriptionChanges) && !isset($memberChanges['email'])) {
                return;
            }
        }

        $attributes = [];

        if (isset($subscriptionChanges['attributes'])) {
            foreach ($subscriptionChanges['attributes'] as $key) {
                $attributes[] = $subscription->getAttributes()[$key];
            }
        }

        if (isset($memberChanges['email'])) {
            $attributes['email'] = $email;
            $email               = $memberChanges['email'][0];
        }

        if (empty($attributes)) {
            return;
        }

        if ($this->api->updateContact($email, $attributes)) {
            return;
        }

        throw new NewsletterException("Failed to update contact through SendInBlue API");
    }

    /**
     * @inheritDoc
     */
    public function deleteSubscription(SubscriptionInterface $subscription): void
    {
        $audienceId = $subscription->getAudience()->getIdentifier();
        $email      = $subscription->getMember()->getEmail();

        if ($this->api->removeContactFromList((int)$audienceId, $email)) {
            return;
        }

        throw new NewsletterException("Failed to remove contact from list through SendInBlue API");
    }

    /**
     * @inheritDoc
     */
    public function supports(string $action): bool
    {
        return in_array($action, [
            self::INSERT_AUDIENCE,
            self::UPDATE_AUDIENCE,
            self::DELETE_AUDIENCE,
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
}
