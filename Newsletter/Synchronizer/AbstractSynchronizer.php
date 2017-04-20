<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Synchronizer;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\Event\SubscriptionEvents;
use Ekyna\Component\Commerce\Newsletter\EventListener\ListenerGatewayToggler;
use Ekyna\Component\Commerce\Newsletter\Factory\AudienceFactoryInterface;
use Ekyna\Component\Commerce\Newsletter\Factory\MemberFactoryInterface;
use Ekyna\Component\Commerce\Newsletter\Factory\SubscriptionFactoryInterface;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\SubscriptionRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractSynchronizer
 * @package Ekyna\Component\Commerce\Newsletter\Synchronizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSynchronizer implements SynchronizerInterface
{
    protected AudienceFactoryInterface        $audienceFactory;
    protected AudienceRepositoryInterface     $audienceRepository;
    protected MemberFactoryInterface          $memberFactory;
    protected MemberRepositoryInterface       $memberRepository;
    protected SubscriptionFactoryInterface    $subscriptionFactory;
    protected SubscriptionRepositoryInterface $subscriptionRepository;
    protected ListenerGatewayToggler          $gatewayToggler;
    protected EventDispatcherInterface        $dispatcher;
    protected EntityManagerInterface          $manager;
    protected UrlGeneratorInterface           $urlGenerator;
    protected LoggerInterface                 $defaultLogger;
    protected LoggerInterface                 $logger;

    /** @var array<int> */
    protected array $audienceIdentifiers;
    /** @var array<MemberInterface> */
    protected array $createdMembers;

    public function __construct(
        AudienceFactoryInterface        $audienceFactory,
        AudienceRepositoryInterface     $audienceRepository,
        MemberFactoryInterface          $memberFactory,
        MemberRepositoryInterface       $memberRepository,
        SubscriptionFactoryInterface    $subscriptionFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        ListenerGatewayToggler          $gatewayToggler,
        EventDispatcherInterface        $dispatcher,
        EntityManagerInterface          $manager,
        UrlGeneratorInterface           $urlGenerator,
        LoggerInterface                 $logger
    ) {
        $this->audienceFactory = $audienceFactory;
        $this->audienceRepository = $audienceRepository;
        $this->memberFactory = $memberFactory;
        $this->memberRepository = $memberRepository;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->gatewayToggler = $gatewayToggler;
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->defaultLogger = $logger;
    }

    public function synchronize(LoggerInterface $logger = null): void
    {
        $this->logger = $logger ?? $this->defaultLogger;

        $this->gatewayToggler->disable();

        $this->audienceIdentifiers = $this->createdMembers = [];

        $this->purgeSubscriptions();

        $this->syncAudiences();

        $this->configureWebhooks();

        $this->purgeAudiences();

        $this->gatewayToggler->enable();
    }

    /**
     * Synchronizes audiences.
     */
    abstract protected function syncAudiences(): void;

    /**
     * Configures webhooks.
     */
    abstract protected function configureWebhooks(): void;

    /**
     * Marks all subscriptions as 'unsubscribed'
     * @noinspection SqlResolve
     */
    protected function purgeSubscriptions(): void
    {
        $this->logger->info('Purging subscriptions');

        // Subscriptions status
        $class = $this->audienceRepository->getClassName();
        $sub = $this->manager->createQuery(
            "SELECT a.id FROM $class a WHERE a.gateway = :gateway"
        )->getDQL();

        $class = $this->subscriptionRepository->getClassName();
        $this->manager->createQuery(
            "UPDATE $class s SET s.status = :unsubscribed WHERE s.audience IN ($sub)"
        )->execute([
            'unsubscribed' => SubscriptionStatus::UNSUBSCRIBED,
            'gateway'      => static::getName(),
        ]);

        // Member status
        $sub = $this->manager->createQuery(
            "SELECT s FROM $class s WHERE s.member = m AND s.status = :subscribed"
        )->getDQL();
        $class = $this->memberRepository->getClassName();
        /** @noinspection SqlSignature */
        $this
            ->manager
            ->createQuery("UPDATE $class m SET m.status = :unsubscribed WHERE NOT EXISTS ($sub)"
            )
            ->execute([
                'subscribed'   => SubscriptionStatus::SUBSCRIBED,
                'unsubscribed' => SubscriptionStatus::UNSUBSCRIBED,
            ]);
    }

    /**
     * Synchronizes the audience.
     *
     * @param string $identifier
     * @param string $name
     *
     * @return AudienceInterface
     */
    protected function syncAudience(string $identifier, string $name): AudienceInterface
    {
        $this->audienceIdentifiers[] = $identifier;

        $audience = $this
            ->audienceRepository
            ->findOneByGatewayAndIdentifier(static::getName(), $identifier);

        if (null === $audience) {
            $audience = $this->audienceFactory->create();
            $audience
                ->setGateway(static::getName())
                ->setIdentifier($identifier)
                ->setName($name)
                ->setTitle($name);

            try {
                $this->audienceRepository->findDefault();
            } catch (RuntimeException $e) {
                $audience
                    ->setPublic(true)
                    ->setDefault(true);
            }

            $this->logger->info(sprintf("Audience '%s': created", $audience->getName()));
            $this->manager->persist($audience);
        } elseif ($this->updateAudience($audience, $identifier, $name)) {
            $this->logger->info(sprintf("Audience '%s': updated", $audience->getName()));
            $this->manager->persist($audience);
        } else {
            $this->logger->info(sprintf("Audience '%s': up to date", $audience->getName()));
        }

        $this->manager->flush();

        return $audience;
    }

    /**
     * Updates the audience.
     *
     * @param AudienceInterface $audience
     * @param string            $identifier
     * @param string            $name
     *
     * @return bool Whether the audience has been changed.
     */
    protected function updateAudience(AudienceInterface $audience, string $identifier, string $name): bool
    {
        $changed = false;

        if ($audience->getIdentifier() !== $identifier) {
            $audience->setIdentifier($identifier);
            $changed = true;
        }

        if ($audience->getName() !== $name) {
            $audience->setName($name);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Synchronizes the member.
     *
     * @param string      $email
     * @param string|null $identifier
     *
     * @return MemberInterface
     */
    protected function syncMember(string $email, string $identifier = null): MemberInterface
    {
        $member = $this->memberRepository->findOneByEmail($email);

        if (null === $member && isset($this->createdMembers[$email])) {
            $member = $this->createdMembers[$email];
        }

        if (null === $member) {
            $member = $this->memberFactory->create();
            $member
                ->setEmail($email)
                ->setIdentifier(static::getName(), $identifier);

            $this->createdMembers[$email] = $member;

            $this->dispatch($member, MemberEvents::PRE_CREATE);

            $this->logger->info(sprintf('Member %s : created', $member->getEmail()));
            $this->manager->persist($member);
        } elseif ($this->updateMember($member, $email, $identifier)) {
            $this->logger->info(sprintf('Member %s : updated', $member->getEmail()));
            $this->manager->persist($member);
        } else {
            $this->logger->info(sprintf("Member '%s': up to date", $member->getEmail()));
        }

        return $member;
    }

    /**
     * Updates the member.
     *
     * @param MemberInterface $member
     * @param string          $email
     * @param string|null     $identifier
     *
     * @return bool Whether the member has been changed.
     */
    protected function updateMember(MemberInterface $member, string $email, string $identifier = null): bool
    {
        $changed = false;

        if ($member->getEmail() !== $email) {
            $member->setEmail($email);
            $changed = true;
        }

        if ($member->getIdentifier(static::getName()) !== $identifier) {
            $member->setIdentifier(static::getName(), $identifier);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Synchronizes the subscription.
     *
     * @param AudienceInterface $audience
     * @param MemberInterface   $member
     * @param string            $status
     * @param array             $attributes
     * @param string|null       $identifier
     *
     * @return SubscriptionInterface
     */
    protected function syncSubscription(
        AudienceInterface $audience,
        MemberInterface   $member,
        string            $status,
        array             $attributes,
        string            $identifier = null
    ): SubscriptionInterface {
        if (!$subscription = $member->getSubscription($audience)) {
            $subscription = $this->subscriptionFactory->create();
            $subscription
                ->setAudience($audience)
                ->setMember($member);

            $this->dispatch($subscription, SubscriptionEvents::PRE_CREATE);
        }

        $this->updateSubscription($subscription, $status, $attributes, $identifier);

        return $subscription;
    }

    /**
     * Updates the subscription.
     *
     * @param SubscriptionInterface $subscription
     * @param string                $status
     * @param array                 $attributes
     * @param string|null           $identifier
     *
     * @return bool Whether the subscription has been changed.
     */
    protected function updateSubscription(
        SubscriptionInterface $subscription,
        string                $status,
        array                 $attributes,
        string                $identifier = null
    ): bool {
        $changed = false;

        if ($status !== $subscription->getStatus()) {
            $subscription->setStatus($status);
            $changed = true;
        }

        if ($attributes !== $subscription->getAttributes()) {
            $subscription->setAttributes($attributes);
            $changed = true;
        }

        if ($identifier !== $subscription->getIdentifier()) {
            $subscription->setIdentifier($identifier);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Dispatches the resource event.
     *
     * @param ResourceInterface $resource
     * @param string            $name
     */
    protected function dispatch(ResourceInterface $resource, string $name): void
    {
        $event = new ResourceEvent();
        $event->setResource($resource);

        $this->dispatcher->dispatch($event, $name);

        if ($event->hasErrors()) {
            $message = array_map(function (ResourceMessage $message) {
                return $message->getMessage();
            }, $event->getErrors());

            throw new NewsletterException($message);
        }
    }

    /**
     * Purges audiences.
     */
    protected function purgeAudiences(): void
    {
        $this->logger->info('Removing audiences');

        $removedAudiences = $this
            ->audienceRepository
            ->findByGatewayExcludingIds(static::getName(), $this->audienceIdentifiers);

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
}
