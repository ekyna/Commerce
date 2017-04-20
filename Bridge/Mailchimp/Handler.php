<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Commerce\Newsletter\Webhook\AbstractHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Handler
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Handler extends AbstractHandler
{
    public function handle(Request $request): Response
    {
        // Reply to GET requests
        if ($request->isMethod('GET')) {
            return new Response('Hey monkey!');
        }

        $key  = (string)$request->attributes->get('key');
        $type = (string)$request->request->get('type');
        $data = (array)$request->request->get('data', []);

        /* Example:
            data[merges][FNAME]: Foo
            data[merges][LNAME]: Bar
            data[email_type]: html
            data[reason]: manual
            data[email]: foo@gmail.com
            data[id]: 4V9c4EvRLk
            data[list_id]: ba039c6198
            data[web_id]: 3375995
        */

        if (empty($type) || empty($data) || empty($key) || !array_key_exists('list_id', $data)) {
            $this->logger->error(sprintf('[%s] %s Unexpected data', Constants::NAME, $type), $data);

            return new Response('Unexpected data', Response::HTTP_NOT_FOUND);
        }

        $audience = $this
            ->audienceRepository
            ->findOneByGatewayAndIdentifier(Constants::NAME, $data['list_id']);

        if (!$audience) {
            $this->logger->error(sprintf('[%s] %s Unknown list', Constants::NAME, $type), $data);

            return new Response('Unknown list', Response::HTTP_NOT_FOUND);
        }

        if ($audience->getKey() !== $key) {
            $this->logger->error(sprintf('[%s] %s Forbidden access', Constants::NAME, $type), $data);

            return new Response('Forbidden access', Response::HTTP_FORBIDDEN);
        }

        switch ($type) {
            case Constants::WEBHOOK_SUBSCRIBE:
                $this->onSubscribe($audience, $data);
                break;
            case Constants::WEBHOOK_UNSUBSCRIBE:
                $this->onUnsubscribe($audience, $data);
                break;
            case Constants::WEBHOOK_PROFILE:
                $this->onProfileUpdate($audience, $data);
                break;
            case Constants::WEBHOOK_CLEANED:
                $this->onCleaned($audience, $data);
                break;
            case Constants::WEBHOOK_UPEMAIL:
                $this->onEmailUpdate($audience, $data);
                break;
            case Constants::WEBHOOK_CAMPAIGN:
                $this->onCampaign($audience, $data);
                break;
            default:
                return new Response('Unexpected webhook type', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'type' => $type,
            'data' => $data,
        ]);
    }

    /**
     * Subscribe event handler.
     */
    private function onSubscribe(AudienceInterface $audience, array $data): void
    {
        $member = $this->memberRepository->findOneByEmail($data['email']);

        if (null === $member) {
            $member = $this->memberFactory->create();
            $member->setEmail($data['email']);
        }

        if (!$subscription = $member->getSubscription($audience)) {
            $subscription = $this->subscriptionFactory->create();
            $subscription
                ->setAudience($audience)
                ->setMember($member);
        }

        $subscription
            ->setIdentifier($data['web_id'])
            ->setStatus(SubscriptionStatus::SUBSCRIBED)
            ->setAttributes($data['merges']);

        $this->persist($member);
    }

    /**
     * Unsubscribe event handler.
     */
    private function onUnsubscribe(AudienceInterface $audience, array $data): void
    {
        $member = $this->memberRepository->findOneByEmail($data['email']);

        if (!$member) {
            return;
        }

        if (!$subscription = $member->getSubscription($audience)) {
            return;
        }

        if (SubscriptionStatus::UNSUBSCRIBED === $subscription->getStatus()) {
            return;
        }

        $subscription->setStatus(SubscriptionStatus::UNSUBSCRIBED);

        $this->persist($member);
    }

    /**
     * Profile update event handler.
     */
    private function onProfileUpdate(AudienceInterface $audience, array $data): void
    {
        $member = $this->memberRepository->findOneByEmail($data['email']);

        if (!$member) {
            return;
        }

        if (!$subscription = $member->getSubscription($audience)) {
            return;
        }

        $subscription
            ->setIdentifier($data['web_id'])
            ->setAttributes($data['merges']);

        $this->persist($member);
    }

    /**
     * Email update event event handler.
     */
    private function onEmailUpdate(AudienceInterface $audience, array $data): void
    {
        if (!$oldMember = $this->memberRepository->findOneByEmail($data['old_email'])) {
            return;
        }

        if (!$oldSubscription = $oldMember->getSubscription($audience)) {
            return;
        }

        $oldMember->removeSubscription($oldSubscription);

        $this->manager->remove($oldSubscription);
        $this->manager->persist($oldMember);

        if (!$newMember = $this->memberRepository->findOneByEmail($data['new_email'])) {
            $newMember = $this->memberFactory->create();
            $newMember->setEmail($data['new_email']);
        }

        if (!$newSubscription = $newMember->getSubscription($audience)) {
            $newSubscription = $this->subscriptionFactory->create();
            $newSubscription
                ->setAudience($audience)
                ->setMember($newMember);
        }

        $newSubscription
            ->setStatus($oldSubscription->getStatus())
            ->setAttributes($oldSubscription->getAttributes());

        $this->persist($newMember);
    }

    /**
     * Cleaned email event handler.
     */
    private function onCleaned(AudienceInterface $audience, array $data): void
    {
        $member = $this->memberRepository->findOneByEmail($data['email']);

        if (!$member) {
            return;
        }

        if (!$subscription = $member->getSubscription($audience)) {
            return;
        }

        $subscription->setStatus(SubscriptionStatus::UNSUBSCRIBED);

        $this->persist($member);
    }

    /**
     * Campaign event handler.
     */
    private function onCampaign(AudienceInterface $audience, array $data): void
    {

    }

    public static function getName(): string
    {
        return Constants::NAME;
    }
}
