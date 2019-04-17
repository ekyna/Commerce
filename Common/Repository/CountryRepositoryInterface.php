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

    /**
     * Finds the codes of the enabled countries.
     *
     * @return array|string[]
     */
    public function findEnabledCodes();

    /**
     * Finds all the country codes.
     *
     * @return array|string[]
     */
    public function findAllCodes();

    /**
     * Returns the country identifiers.
     *
     * @param bool $cached Whether to return only cached countries identifiers
     *
     * @return array
     */
    public function getIdentifiers($cached = false);
}
