<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Payment\Entity\PaymentMessage;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Repository\PaymentMethodRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class PaymentMethodRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodRepository extends TranslatableResourceRepository implements PaymentMethodRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface $method */
        $method = parent::createNew();

        foreach (PaymentStates::getNotifiableStates() as $state) {
            $message = new PaymentMessage();
            $method->addMessage($message->setState($state));
        }

        return $method;
    }

    /**
     * @inheritDoc
     */
    public function findAvailable()
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
            ->andWhere($qb->expr()->eq('m.available', ':available'))
            ->getQuery()
            ->setParameters([
                'enabled'   => true,
                'available' => true,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findEnabled()
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
            ->getQuery()
            ->setParameters([
                'enabled' => true,
            ])
            ->getResult();
    }
}
