<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CurrencyRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyRepository extends ResourceRepository implements CurrencyRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findDefault()
    {
        if (null !== $defaultCurrency = $this->findOneBy(['default' => true])) {
            return $defaultCurrency;
        }

        throw new RuntimeException('Default currency not found.');
    }

    /**
     * @inheritdoc
     */
    public function findOneByCode($code)
    {
        return $this
            ->getQueryBuilder('c')
            ->andWhere('c.code = :code')
            ->getQuery()
            ->setParameter('code', strtoupper($code))
            ->getOneOrNullResult();
    }
}
