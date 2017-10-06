<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
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
     * @var string
     */
    private $defaultCode;

    /**
     * @var CountryInterface
     */
    private $defaultCountry;


    /**
     * Sets the default code.
     *
     * @param string $code
     */
    public function setDefaultCode($code)
    {
        $this->defaultCode = $code;
    }

    /**
     * @inheritdoc
     */
    public function findDefault()
    {
        if (null !== $this->defaultCountry) {
            return $this->defaultCountry;
        }

        if (null !== $country = $this->findOneByCode($this->defaultCode)) {
            return $this->defaultCountry = $country;
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
