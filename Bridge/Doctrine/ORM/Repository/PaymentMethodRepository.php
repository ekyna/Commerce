<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMessage;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
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
    public function findAvailable(CurrencyInterface $currency = null): array
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        $parameters = [
            'enabled'   => true,
            'available' => true,
            'private'   => false,
        ];

        $qb
            ->andWhere($ex->eq('m.enabled', ':enabled'))
            ->andWhere($ex->eq('m.available', ':available'))
            ->andWhere($ex->eq('m.private', ':private'))
            ->orderBy('m.position', 'ASC');

        if ($currency) {
            $parameters['currency'] = $currency;
            $qb->andWhere($ex->orX(
                $qb->expr()->isMemberOf(':currency', 'm.currencies'),
                'm.currencies IS EMPTY'
            ));
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findEnabled(CurrencyInterface $currency = null): array
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        $parameters = [
            'enabled' => true,
            'private' => false,
        ];

        $qb
            ->andWhere($ex->eq('m.enabled', ':enabled'))
            ->andWhere($ex->eq('m.private', ':private'))
            ->orderBy('m.position', 'ASC');

        if ($currency) {
            $parameters['currency'] = $currency;
            $qb->andWhere($ex->orX(
                $qb->expr()->isMemberOf(':currency', 'm.currencies'),
                'm.currencies IS EMPTY'
            ));
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByFactoryName(string $name): ?PaymentMethodInterface
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->andWhere($qb->expr()->eq('m.factoryName', ':name'))
            ->getQuery()
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }
}
