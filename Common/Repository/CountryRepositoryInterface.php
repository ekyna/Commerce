<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface CountryRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CountryRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the default code.
     *
     * @return string
     */
    public function getDefaultCode(): string;

    /**
     * Sets the default code.
     *
     * @param string $code
     */
    public function setDefaultCode(string $code): void;

    /**
     * Sets the cached codes.
     *
     * @param array $codes
     */
    public function setCachedCodes(array $codes): void;

    /**
     * Returns the default country.
     *
     * @return CountryInterface
     */
    public function findDefault(): CountryInterface;

    /**
     * Finds a country by its code.
     *
     * @param string $code
     *
     * @return CountryInterface|null
     */
    public function findOneByCode(string $code): ?CountryInterface;

    /**
     * Finds the codes of the enabled countries.
     *
     * @return array<string>
     */
    public function findEnabledCodes(): array;

    /**
     * Finds all the country codes.
     *
     * @return array<string>
     */
    public function findAllCodes(): array;

    /**
     * Returns all the countries names.
     *
     * @return array<string, string>
     */
    public function getNames(bool $enabled): array;

    /**
     * Returns the country identifiers.
     *
     * @param bool $cached Whether to return only cached countries identifiers
     *
     * @return array<int>
     */
    public function getIdentifiers(bool $cached = false): array;
}
