<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Event\AudienceEvents;
use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\EventListener\ListenerGatewayToggler;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Synchronizer\SynchronizerInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Synchronizer
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Synchronizer implements SynchronizerInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var AudienceRepositoryInterface
     */
    private $audienceRepository;

    /**
     * @var MemberRepositoryInterface
     */
    private $memberRepository;

    /**
     * @var ListenerGatewayToggler
     */
    private $gatewayToggler;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var LoggerInterface
     */
    private $defaultLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $audienceIdentifiers;


    /**
     * Constructor.
     *
     * @param Api                         $api
     * @param AudienceRepositoryInterface $audienceRepository
     * @param MemberRepositoryInterface   $memberRepository
     * @param EventDispatcherInterface    $dispatcher
     * @param ListenerGatewayToggler      $gatewayToggler
     * @param EntityManagerInterface      $manager
     * @param UrlGeneratorInterface       $urlGenerator
     * @param LoggerInterface             $logger
     */
    public function __construct(
        Api $api,
        AudienceRepositoryInterface $audienceRepository,
        MemberRepositoryInterface $memberRepository,
        ListenerGatewayToggler $gatewayToggler,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->api                = $api;
        $this->audienceRepository = $audienceRepository;
        $this->memberRepository   = $memberRepository;
        $this->gatewayToggler     = $gatewayToggler;
        $this->dispatcher         = $dispatcher;
        $this->manager            = $manager;
        $this->urlGenerator       = $urlGenerator;
        $this->defaultLogger      = $logger;
    }

    /**
     * @inheritDoc
     */
    public function synchronize(LoggerInterface $logger = null): void
    {
        $this->logger = $logger ?? $this->defaultLogger;

        $this->audienceIdentifiers = [];

        $this->gatewayToggler->disable();

        $this->syncAudiences();

        $this->configureWebhooks();

        $this->purgeAudiences();

        $this->gatewayToggler->enable();
    }

    /**
     * Synchronizes audiences.
     */
    protected function syncAudiences(): void
    {
        $this->logger->info('Synchronizing audiences');

        $page = 0;
        while (!empty($data = $this->api->getAudiences(20, $page * 20))) {
            $page++;

            foreach ($data as $datum) {
                $audienceIdentifiers[] = $identifier = (string)$datum['id'];

                $audience = $this
                    ->audienceRepository
                    ->findOneByGatewayAndIdentifier(Constants::NAME, $identifier);

                if (null === $audience) {
                    /** @var AudienceInterface $audience */
                    $audience = $this->audienceRepository->createNew();
                    $audience
                        ->setGateway(Constants::NAME)
                        ->setIdentifier($identifier)
                        ->setTitle((string)$datum['name']);

                    try {
                        $this->audienceRepository->findDefault();
                    } catch (RuntimeException $e) {
                        $audience
                            ->setPublic(true)
                            ->setDefault(true);
                    }

                    $this->initialize($audience);
                }

                if ($this->syncAudience($audience, $datum)) {
                    $this->logger->info(sprintf("Audience '%s': updated", $audience->getName()));
                    $this->manager->persist($audience);
                } else {
                    $this->logger->info(sprintf("Audience '%s': up to date", $audience->getName()));
                }

                $this->manager->flush();

                $this->syncMembers($audience);
            }
        }
    }

    /**
     * Configures audience's webhooks.
     */
    protected function configureWebhooks(): void
    {
        $this->logger->info('Configuring webhooks');

        $count     = 0;
        $audiences = $this->audienceRepository->findByGatewayWithWebhookNotConfigured(Constants::NAME);

        foreach ($audiences as $audience) {
            $identifier = $audience->getIdentifier();

            $url = $this->urlGenerator->generate(
                'ekyna_commerce_api_newsletter_webhook',
                [
                    'name' => Constants::NAME,
                    'key'  => $audience->getKey(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $result = $this->api->post("lists/$identifier/webhooks", [
                'url'     => $url,
                'events'  => array_fill_keys(Constants::getWebhooks(), true),
                'sources' => array_fill_keys([Constants::SOURCE_USER, Constants::SOURCE_ADMIN], true),
            ]);

            if (!$this->api->success()) {
                $this->api->logError($result);

                continue;
            }

            $audience->setWebhook(true);
            $this->manager->persist($audience);
            $count++;

            $this->logger->info(sprintf("Audience '%s' : configured", $audience->getName()));
        }

        if (0 < $count) {
            $this->manager->flush();
        }

        $this->manager->clear();
    }

    /**
     * Purges audiences.
     */
    protected function purgeAudiences(): void
    {
        $this->logger->info('Removing audiences');

        $removedAudiences = $this
            ->audienceRepository
            ->findByGatewayExcludingIds(Constants::NAME, $this->audienceIdentifiers);

        if (empty($removedAudiences)) {
            $this->logger->info('No audience to remove.');

            return;
        }

        foreach ($removedAudiences as $audience) {
            $this->logger->info(sprintf("Audience '%s': removed", $audience->getName()));
            $this->manager->remove($audience);
        }

        $this->manager->flush();
        $this->manager->clear();
    }

    /**
     * Synchronizes the given audience and data.
     *
     * @param AudienceInterface $audience
     * @param array             $data
     *
     * @return bool Whether the audience has been changed.
     */
    protected function syncAudience(AudienceInterface $audience, array $data): bool
    {
        $changed = false;

        if ($audience->getIdentifier() !== $identifier = (string)$data['id']) {
            $audience->setIdentifier($identifier);
            $changed = true;
        }

        if ($audience->getName() !== $name = (string)$data['name']) {
            $audience->setName($name);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Synchronizes audience's members.
     *
     * @param AudienceInterface $audience
     */
    protected function syncMembers(AudienceInterface $audience): void
    {
        $page = 0;
        while (!empty($data = $this->api->getAudienceMembers($audience, 20, $page * 20))) {
            $page++;

            foreach ($data as $datum) {
                $identifier = (string)$datum['web_id'];

                $member = $this
                    ->memberRepository
                    ->findOneByGatewayAndIdentifier(Constants::NAME, $identifier);

                if (null === $member) {
                    /** @var MemberInterface $member */
                    $member = $this->memberRepository->createNew();
                    $member
                        ->setAudience($audience)
                        ->setIdentifier($identifier);

                    $this->initialize($member);
                }

                if ($this->syncMember($audience, $member, $datum)) {
                    $this->logger->info(sprintf('Member %s : updated', $member->getEmail()));
                    $this->manager->persist($member);
                } else {
                    $this->logger->info(sprintf('Member %s : up to date', $member->getEmail()));
                }
            }

            $this->manager->flush();
        }
    }

    /**
     * Synchronizes the given member and data.
     *
     * @param AudienceInterface $audience
     * @param MemberInterface   $member
     * @param array             $data
     *
     * @return bool
     */
    protected function syncMember(AudienceInterface $audience, MemberInterface $member, array $data): bool
    {
        $changed = false;

        if ($audience !== $member->getAudience()) {
            $member->setAudience($audience);
            $changed = true;
        }

        if ($member->getIdentifier() !== $identifier = (string)$data['web_id']) {
            $member->setIdentifier($identifier);
            $changed = true;
        }

        if ($member->getEmail() !== $address = (string)$data['email_address']) {
            $member->setEmail($address);
            $changed = true;
        }

        $status = 'subscribed' === $data['status'] ? MemberStatuses::SUBSCRIBED : MemberStatuses::UNSUBSCRIBED;
        if ($member->getStatus() !== $status) {
            $member->setStatus($status);
            $changed = true;
        }

        if ($member->getAttributes() != $data['merge_fields']) {
            $member->setAttributes($data['merge_fields']);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Initializes the resource.
     *
     * @param ResourceInterface $resource
     *
     * @throws LogicException
     */
    protected function initialize(ResourceInterface $resource): void
    {
        if ($resource instanceof AudienceInterface) {
            $name = AudienceEvents::INITIALIZE;
        } elseif ($resource instanceof MemberInterface) {
            $name = MemberEvents::INITIALIZE;
        } else {
            throw new LogicException("Unexpected resource.");
        }

        $event = new ResourceEvent();
        $event->setResource($resource);

        $this->dispatcher->dispatch($name, $event);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return Constants::NAME;
    }
}
