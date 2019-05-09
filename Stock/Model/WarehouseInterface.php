<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Stock\Entity\Warehouse;
use Ekyna\Component\Resource\Model\IsDefaultInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface WarehouseInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WarehouseInterface extends ResourceInterface, AddressInterface, IsDefaultInterface
{
    /**
     * Returns the name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|WarehouseInterface
     */
    public function setName(string $name): WarehouseInterface;

    /**
     * Returns the countries.
     *
     * @return Collection|CountryInterface[]
     */
    public function getCountries(): Collection;

    /**
     * Adds the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|WarehouseInterface
     */
    public function addCountry(CountryInterface $country): WarehouseInterface;

    /**
     * Removes the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|WarehouseInterface
     */
    public function removeCountry(CountryInterface $country): WarehouseInterface;

    /**
     * Returns the office.
     *
     * @return bool
     */
    public function isOffice(): bool;

    /**
     * Sets the office.
     *
     * @param bool $office
     *
     * @return $this|WarehouseInterface
     */
    public function setOffice(bool $office): WarehouseInterface;

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Sets the priority.
     *
     * @param int $priority
     *
     * @return Warehouse
     */
    public function setPriority(int $priority): WarehouseInterface;
}
