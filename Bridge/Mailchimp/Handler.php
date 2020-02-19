<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Newsletter\EventListener\ListenerToggler;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Webhook\HandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Handler
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Handler implements HandlerInterface
{
    /**
     * @var AudienceRepositoryInterface
     */
    private $audienceRepository;

    /**
     * @var MemberRepositoryInterface
     */
    private $memberRepository;

    /**
     * @var ListenerToggler
     */
    private $listenerToggler;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Constructor.
     *
     * @param AudienceRepositoryInterface $audienceRepository
     * @param MemberRepositoryInterface   $memberRepository
     * @param ListenerToggler             $listenerToggler
     * @param EntityManagerInterface      $manager
     * @param LoggerInterface             $logger
     */
    public function __construct(
        AudienceRepositoryInterface $audienceRepository,
        MemberRepositoryInterface $memberRepository,
        ListenerToggler $listenerToggler,
        EntityManagerInterface $manager,
        LoggerInterface $logger
    ) {
        $this->audienceRepository = $audienceRepository;
        $this->memberRepository   = $memberRepository;
        $this->listenerToggler    = $listenerToggler;
        $this->manager            = $manager;
        $this->logger             = $logger;
    }

    /**
     * @inheritDoc
     */
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
                break;
        }

        return new JsonResponse([
            'type' => $type,
            'data' => $data,
        ]);
    }

    /**
     * Subscribe event handler.
     *
     * @param AudienceInterface $audience
     * @param array $data
     */
    public function onSubscribe(AudienceInterface $audience, array $data): void
    {
        $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $data['email']);

        if (null === $member) {
            /** @var MemberInterface $member */
            $member = $this->memberRepository->createNew();
            $member
                ->setAudience($audience)
                ->setIdentifier($data['web_id'])
                ->setEmail($data['email'])
                ->setAttributes($data['merges']);
        }

        $member->setStatus(MemberStatuses::SUBSCRIBED);

        $this->persist($member);
    }

    /**
     * Unsubscribe event handler.
     *
     * @param AudienceInterface $audience
     * @param array $data
     */
    public function onUnsubscribe(AudienceInterface $audience, array $data): void
    {
        $member = $this
            ->memberRepository
            ->findOneByAudienceAndEmail($audience, $data['email']);

        if (!$member) {
            return;
        }

        $member->setStatus(MemberStatuses::UNSUBSCRIBED);

        $this->persist($member);
    }

    /**
     * Profile update event handler.
     *
     * @param AudienceInterface $audience
     * @param array $data
     */
    public function onProfileUpdate(AudienceInterface $audience, array $data): void
    {
        $member = $this
            ->memberRepository
            ->findOneByAudienceAndEmail($audience, $data['email']);

        if (!$member) {
            return;
        }

        $member
            ->setIdentifier($data['web_id'])
            ->setAttributes($data['merges']);

        $this->persist($member);
    }

    /**
     * Email update event event handler.
     *
     * @param AudienceInterface $audience
     * @param array $data
     */
    public function onEmailUpdate(AudienceInterface $audience, array $data): void
    {
        $member = $this
            ->memberRepository
            ->findOneByAudienceAndEmail($audience, $data['old_email']);

        $member
            ->setIdentifier(Api::subscriberHash($data['new_email']))
            ->setEmail($data['new_email']);

        $this->persist($member);
    }

    /**
     * Cleaned email event handler.
     *
     * @param AudienceInterface $audience
     * @param array $data
     */
    public function onCleaned(AudienceInterface $audience, array $data): void
    {
        $member = $this
            ->memberRepository
            ->findOneByAudienceAndEmail($audience, $data['email']);

        if (!$member) {
            return;
        }

        $member->setStatus(MemberStatuses::UNSUBSCRIBED);

        $this->persist($member);
    }

    /**
     * Campaign event handler.
     *
     * @param AudienceInterface $audience
     * @param array $data
     */
    public function onCampaign(AudienceInterface $audience, array $data): void
    {

    }

    /**
     * Persists the member.
     *
     * @param MemberInterface $member
     */
    private function persist(MemberInterface $member): void
    {
        $this->listenerToggler->disable();

        $this->manager->persist($member);
        $this->manager->flush();
        $this->manager->clear();

        $this->listenerToggler->enable();
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return Constants::NAME;
    }
}
