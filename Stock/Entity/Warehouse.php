<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Resource\Model\IsDefaultTrait;

/**
 * Class Warehouse
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Warehouse extends AbstractAddress implements WarehouseInterface
{
    use IsDefaultTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection
     */
    protected $countries;

    /**
     * @var bool
     */
    protected $office = false;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var int
     */
    protected $priority = 0;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->countries = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->name ?: 'New warehouse';
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): WarehouseInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    /**
     * @inheritDoc
     */
    public function addCountry(CountryInterface $country): WarehouseInterface
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeCountry(CountryInterface $country): WarehouseInterface
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isOffice(): bool
    {
        return $this->office;
    }

    /**
     * @inheritDoc
     */
    public function setOffice(bool $office): WarehouseInterface
    {
        $this->office = $office;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @inheritDoc
     */
    public function setEnabled(bool $enabled): WarehouseInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritDoc
     */
    public function setPriority(int $priority): WarehouseInterface
    {
        $this->priority = $priority;

        return $this;
    }
}
