<?php

namespace Ekyna\Component\Commerce\Bridge\SendInBlue;

use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Commerce\Newsletter\Webhook\AbstractHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class Handler
 * @package Ekyna\Component\Commerce\Bridge\SendInBlue
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Handler extends AbstractHandler
{
    /**
     * @inheritDoc
     */
    public function handle(Request $request): Response
    {
        $data = \json_decode($request->getContent(), true);

        $this->logger->error('[SendInBlue webhook] ' . $request->getContent());

        if (!isset($data['event'])) {
            throw new BadRequestHttpException('Unknown webhook event');
        }

        switch ($data['event']) {
            case Constants::WEBHOOK_CONTACT_ADDED:
                $this->addContact($data);
                break;

            case Constants::WEBHOOK_CONTACT_UPDATED:
                $this->updateContact($data);
                break;

            case Constants::WEBHOOK_CONTACT_DELETED:
                $this->deleteContact($data);
                break;

            case Constants::WEBHOOK_UNSUBSCRIBED:
                $this->unsubscribe($data);
                break;

            case Constants::WEBHOOK_HARD_BOUNCED:
            case Constants::WEBHOOK_SOFT_BOUNCED:
            case Constants::WEBHOOK_DELIVERED:
            case Constants::WEBHOOK_MARKED_AS_SPAM:
            case Constants::WEBHOOK_OPENED:
            case Constants::WEBHOOK_CLICKED:
                throw new BadRequestHttpException('Unsupported webhook event');
        }

        return new Response();
    }

    private function addContact(array $data): void
    {
        /* {
         *   "id":139911,
         *   "email":"lu.sykora@demo.domain",
         *   "event":"list_addition",
         *   "key":"fsn920nfsv6h0gfkkqrb5",
         *   "list_id":[63,61],
         *   "date":"2019-07-30 08:48:11",
         *   "ts":1564469292
         * } */

        sleep(1);

        $member = $this->memberRepository->findOneByEmail($data['email']);
        if (null === $member) {
            $member = $this->memberRepository->createNew();
            $member
                ->setIdentifier(static::getName(), $data['id'])
                ->setEmail($data['email']);
            //->setAttributes($data['merges']);
        }

        foreach ($data['list_id'] as $identifier) {
            $audience = $this->audienceRepository->findOneByGatewayAndIdentifier(Constants::NAME, $identifier);
            if (null === $audience) {
                continue;
            }

            if (null === $subscription = $member->getSubscription($audience)) {
                $subscription = $this->subscriptionRepository->createNew();
                $subscription
                    ->setMember($member)
                    ->setAudience($audience);
            }

            $subscription->setStatus(SubscriptionStatus::SUBSCRIBED);
        }

        $this->persist($member);
    }

    private function updateContact(array $data): void
    {
        /* {
         *   "id":139911,
         *   "event":"contact_updated",
         *   "key":"fsn920nfsv6h0gfkkqrb5",
         *   "date":"2019-07-30 08:52:02",
         *   "ts":1564469523,
         *   "content":[
         *     {
         *       "email":"lu.sykora@demo.domain",
         *       "attributes":{
         *         "FIRSTNAME":"Luke",
         *         "LASTNAME":"Sykora",
         *         "SMS":"30764261246"
         *       },
         *       "list":{
         *         "addition":[
         *           {"id":2,"name":"sendinblue ekyna"}
         *         ],
         *         "deletion":[]
         *       }
         *     }
         *   ]
         * } */

        sleep(1);

        $contents = $data['content'];

        foreach ($contents as $content) {
            $persist = false;
            $member = $this->memberRepository->findOneByEmail($content['email']);

            if (null === $member) {
                // TODO Create ?
                continue;
            }

            // Attributes
            if (isset($content['attributes'])) {
                foreach ($member->getSubscriptions() as $subscription) {
                    if (Constants::NAME !== $subscription->getAudience()->getGateway()) {
                        continue;
                    }

                    $subscription->setAttributes((array)$content['attributes']);

                    $persist = true;
                }
            }

            // Lists
            if (isset($content['list'])) {
                // Addition
                if (!empty($content['list']['addition'])) {
                    foreach ($content['list']['addition'] as $addition) {
                        $audience = $this
                            ->audienceRepository
                            ->findOneByGatewayAndIdentifier(Constants::NAME, $addition['id']);

                        if (!$audience) {
                            continue;
                        }

                        if ($subscription = $member->getSubscription($audience)) {
                            if ($subscription->getStatus() === SubscriptionStatus::SUBSCRIBED) {
                                continue;
                            }

                            $subscription->setStatus(SubscriptionStatus::SUBSCRIBED);

                            $persist = true;

                            continue;
                        }

                        $subscription = $this->subscriptionRepository->createNew();
                        $subscription
                            ->setAudience($audience)
                            ->setMember($member)
                            ->setStatus(SubscriptionStatus::SUBSCRIBED);
                            // TODO ->setAttributes()

                        $persist = true;
                    }
                }

                // Deletion
                if (!empty($content['list']['deletion'])) {
                    foreach ($content['list']['deletion'] as $addition) {
                        $audience = $this
                            ->audienceRepository
                            ->findOneByGatewayAndIdentifier(Constants::NAME, $addition['id']);

                        if (!$audience) {
                            continue;
                        }

                        if (!$subscription = $member->getSubscription($audience)) {
                            continue;
                        }

                        if ($subscription->getStatus() === SubscriptionStatus::UNSUBSCRIBED) {
                            continue;
                        }

                        $subscription->setStatus(SubscriptionStatus::UNSUBSCRIBED);

                        $persist = true;
                    }
                }
            }

            if ($persist) {
                $this->persist($member);
            }
        }
    }

    private function deleteContact(array $data): void
    {
        /* {
         *   "id":139911,
         *   "email":["lu.sykora@demo.domain"],
         *   "event":"contact_deleted",
         *   "key":"fsn920nfsv6h0gfkkqrb5",
         *   "date":"2019-07-30 08:53:17",
         *   "ts":1564469598
         * } */

        sleep(1);

        foreach ($data['email'] as $email) {
            $member = $this->memberRepository->findOneByEmail($email);

            if (!$member) {
                continue;
            }

            $found = false;
            foreach ($member->getSubscriptions() as $subscription) {
                if (Constants::NAME !== $subscription->getAudience()->getGateway()) {
                    continue;
                }

                $member->removeSubscription($subscription);

                $this->manager->remove($subscription);

                $found = true;
            }

            if ($found) {
                $this->persist($member);
            }
        }
    }

    private function unsubscribe(array $data): void
    {
        /* {
         *   "id":139911,
         *   "camp_id":253,
         *   "email":"abc@example.com",
         *   "campaign name":"Campaign ABC",
         *   "date_sent":"2019-07-30 11:30:50",
         *   "date_event":"2019-07-30 11:39:15",
         *   "event":"unsubscribe",
         *   "tag":"abc",
         *   "list_id":[63,61],
         *   "ts_sent":1564479050,
         *   "ts_event":1564479555,
         *   "ts":1564466956
         * } */

        sleep(1);

        $member = $this->memberRepository->findOneByEmail($data['email']);

        if (!$member) {
            return;
        }

        $found = false;
        foreach ($member->getSubscriptions() as $subscription) {
            $audience = $subscription->getAudience();
            if (Constants::NAME !== $audience->getGateway()) {
                continue;
            }

            if (!in_array($audience->getIdentifier(), $data['list_id'])) {
                continue;
            }

            $subscription->setStatus(SubscriptionStatus::UNSUBSCRIBED);

            $found = true;
        }

        if ($found) {
            $this->persist($member);
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
