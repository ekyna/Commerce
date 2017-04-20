<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentMethodRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class PaymentMethodRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class PaymentMethodRepository extends TranslatableRepository implements PaymentMethodRepositoryInterface
{
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
}
