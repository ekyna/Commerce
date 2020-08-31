<?php

namespace Ekyna\Component\Commerce\Newsletter\Webhook;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Newsletter\EventListener\ListenerGatewayToggler;
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
    /**
     * @var AudienceRepositoryInterface
     */
    protected $audienceRepository;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    /**
     * @var SubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;

    /**
     * @var ListenerGatewayToggler
     */
    protected $gatewayToggler;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Constructor.
     *
     * @param AudienceRepositoryInterface     $audienceRepository
     * @param MemberRepositoryInterface       $memberRepository
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param ListenerGatewayToggler          $gatewayToggler
     * @param EntityManagerInterface          $manager
     * @param LoggerInterface                 $logger
     */
    public function __construct(
        AudienceRepositoryInterface $audienceRepository,
        MemberRepositoryInterface $memberRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        ListenerGatewayToggler $gatewayToggler,
        EntityManagerInterface $manager,
        LoggerInterface $logger
    ) {
        $this->audienceRepository     = $audienceRepository;
        $this->memberRepository       = $memberRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->gatewayToggler         = $gatewayToggler;
        $this->manager                = $manager;
        $this->logger                 = $logger;
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
