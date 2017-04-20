<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Commerce\Newsletter\Synchronizer\AbstractSynchronizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Synchronizer
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
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
     * Synchronizes audiences.
     */
    protected function syncAudiences(): void
    {
        $this->logger->info('Synchronizing audiences');

        $page = 0;
        while ($data = $this->api->getAudiences(20, $page * 20)) {
            $page++;

            foreach ($data as $datum) {
                $identifier = (string)$datum['id'];
                $name       = (string)$datum['name'];

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
        $this->logger->info('Configuring webhooks');

        $count     = 0;
        $audiences = $this->audienceRepository->findByGateway(Constants::NAME);

        foreach ($audiences as $audience) {
            $identifier = $audience->getIdentifier();

            $url = $this->urlGenerator->generate(
                'ekyna_commerce_api_newsletter_webhook_audience',
                [
                    'name' => Constants::NAME,
                    'key'  => $audience->getKey(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $result = $this->api->get("lists/$identifier/webhooks");
            if (isset($result['webhooks'])) {
                foreach ($result['webhooks'] as $webhook) {
                    if ($webhook['url'] === $url) {
                        continue 2;
                    }
                }
            }

            $result = $this->api->post("lists/$identifier/webhooks", [
                'url'     => $url,
                'events'  => array_fill_keys(Constants::getWebhooks(), true),
                'sources' => array_fill_keys([Constants::SOURCE_USER, Constants::SOURCE_ADMIN], true),
            ]);

            if (!$this->api->success()) {
                $this->api->logError($result);

                continue;
            }

            $count++;

            $this->logger->info(sprintf("Audience '%s' : configured", $audience->getName()));
        }
    }

    /**
     * Synchronizes audience's members.
     *
     * @param AudienceInterface $audience
     */
    private function syncMembers(AudienceInterface $audience): void
    {
        $page = 0;
        while ($data = $this->api->getAudienceMembers($audience, 20, $page * 20)) {
            $page++;

            foreach ($data as $datum) {
                $identifier = (string)$datum['web_id'];
                $email      = (string)$datum['email_address'];

                $member = $this->syncMember($email);

                $status = 'subscribed' === $datum['status']
                    ? SubscriptionStatus::SUBSCRIBED
                    : SubscriptionStatus::UNSUBSCRIBED;

                $this->syncSubscription($audience, $member, $status, (array)$datum['merge_fields'], $identifier);
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
