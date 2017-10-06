<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
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
     * @var string
     */
    private $defaultCode;

    /**
     * @var CurrencyInterface
     */
    private $defaultCurrency;


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
        if (null !== $this->defaultCurrency) {
            return $this->defaultCurrency;
        }

        if (null !== $currency = $this->findOneByCode($this->defaultCode)) {
            return $this->defaultCurrency = $currency;
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
