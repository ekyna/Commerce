<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Webhook;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Newsletter\EventListener\ListenerGatewayToggler;
use Ekyna\Component\Commerce\Newsletter\Factory\MemberFactoryInterface;
use Ekyna\Component\Commerce\Newsletter\Factory\SubscriptionFactoryInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\SubscriptionRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractHandler
 * @package Ekyna\Component\Commerce\Newsletter\Webhook
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractHandler implements HandlerInterface
{
    protected AudienceRepositoryInterface     $audienceRepository;
    protected MemberFactoryInterface          $memberFactory;
    protected MemberRepositoryInterface       $memberRepository;
    protected SubscriptionFactoryInterface    $subscriptionFactory;
    protected SubscriptionRepositoryInterface $subscriptionRepository;
    protected ListenerGatewayToggler          $gatewayToggler;
    protected EntityManagerInterface          $manager;
    protected LoggerInterface                 $logger;

    public function __construct(
        AudienceRepositoryInterface     $audienceRepository,
        MemberFactoryInterface          $memberFactory,
        MemberRepositoryInterface       $memberRepository,
        SubscriptionFactoryInterface    $subscriptionFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        ListenerGatewayToggler          $gatewayToggler,
        EntityManagerInterface          $manager,
        LoggerInterface                 $logger
    ) {
        $this->audienceRepository = $audienceRepository;
        $this->memberFactory = $memberFactory;
        $this->memberRepository = $memberRepository;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->gatewayToggler = $gatewayToggler;
        $this->manager = $manager;
        $this->logger = $logger;
    }

    /**
     * Persists the member.
     *
     * @param MemberInterface $member
     */
    protected function persist(MemberInterface $member): void
    {
        $this->gatewayToggler->disable();

        $this->manager->persist($member);
        $this->manager->flush();
        $this->manager->clear();

        $this->gatewayToggler->enable();
    }
}
