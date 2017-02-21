<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CountryRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryRepository extends ResourceRepository implements CountryRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findDefault()
    {
        if (null !== $defaultCountry = $this->findOneBy(['default' => true])) {
            return $defaultCountry;
        }

        throw new RuntimeException('Default country not found.');
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
