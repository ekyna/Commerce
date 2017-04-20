<?php

namespace Ekyna\Component\Commerce\Bridge\SendInBlue;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Commerce\Newsletter\Synchronizer\AbstractSynchronizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Synchronizer
 * @package Ekyna\Component\Commerce\Bridge\SendInBlue
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Synchronizer extends AbstractSynchronizer
{
    /**
     * @var Api
     */
    private $api;


    /**
     * Sets the api.
     *
     * @param Api $api
     */
    public function setApi(Api $api): void
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    protected function syncAudiences(): void
    {
        $this->logger->info('Synchronizing audiences');

        $page = 0;
        while ($lists = $this->api->getLists(20, $page * 20)) {
            $page++;

            foreach ($lists as $list) {
                $identifier = (string)$list['id'];
                $name       = (string)$list['name'];

                $audience = $this->syncAudience($identifier, $name);

                $this->syncMembers($audience);

                $this->manager->flush();
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function configureWebhooks(): void
    {
        $url = $this->urlGenerator->generate(
            'ekyna_commerce_api_newsletter_webhook',
            ['name' => Constants::NAME,],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if ($webhooks = $this->api->listWebhooks()) {
            foreach ($webhooks as $webhook) {
                if ($webhook['url'] === $url) {
                    $this->logger->info("Webhook is already configured");

                    return;
                }
            }
        }

        $result = $this->api->createWebhook(
            [
                'listAddition',
                'contactUpdated',
                'contactDeleted',
                'unsubscribed',
            ],
            'marketing',
            $url
        );

        if (!$result) {
            $this->logger->info("Failed to configure webhook");

            return;
        }

        $this->logger->info("Webhook configured");
    }

    /**
     * Synchronizes audience's members.
     *
     * @param AudienceInterface $audience
     */
    private function syncMembers(AudienceInterface $audience): void
    {
        $page = 0;
        while ($contacts = $this->api->getListContacts($audience, 20, $page * 20)) {
            $page++;

            foreach ($contacts as $contact) {
                $identifier = (string)$contact['id'];
                $email      = (string)$contact['email'];

                $member = $this->syncMember($email, $identifier);

                $status = in_array($audience->getIdentifier(), $contact['listIds'])
                    ? SubscriptionStatus::SUBSCRIBED
                    : SubscriptionStatus::UNSUBSCRIBED;

                $this->syncSubscription($audience, $member, $status, (array)$contact['attributes']);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return Constants::NAME;
    }
}
