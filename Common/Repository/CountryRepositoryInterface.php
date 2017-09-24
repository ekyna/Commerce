<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface CountryRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CountryRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the default country.
     *
     * @return CountryInterface
     */
    public function findDefault();

    /**
     * Finds a country by its code.
     *
     * @param string $code
     *
     * @return CountryInterface
     */
    public function findOneByCode($code);
}
