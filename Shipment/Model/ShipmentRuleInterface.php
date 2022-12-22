<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ShipmentRuleInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentRuleInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): ShipmentRuleInterface;

    /**
     * @return Collection<int, ShipmentMethodInterface>
     */
    public function getMethods(): Collection;

    public function addMethod(ShipmentMethodInterface $method): ShipmentRuleInterface;

    public function removeMethod(ShipmentMethodInterface $method): ShipmentRuleInterface;

    /**
     * @return Collection<int, CountryInterface>
     */
    public function getCountries(): Collection;

    public function addCountry(CountryInterface $country): ShipmentRuleInterface;

    public function removeCountry(CountryInterface $country): ShipmentRuleInterface;

    /**
     * @return Collection<int, ShipmentMethodInterface>
     */
    public function getCustomerGroups(): Collection;

    public function addCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface;

    public function removeCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface;

    public function getBaseTotal(): Decimal;

    public function setBaseTotal(Decimal $total): ShipmentRuleInterface;

    public function getVatMode(): string;

    public function setVatMode(string $mode): ShipmentRuleInterface;

    public function getStartAt(): ?DateTimeInterface;

    public function setStartAt(?DateTimeInterface $date): ShipmentRuleInterface;

    public function getEndAt(): ?DateTimeInterface;

    public function setEndAt(?DateTimeInterface $date): ShipmentRuleInterface;

    public function getNetPrice(): Decimal;

    public function setNetPrice(Decimal $price): ShipmentRuleInterface;
}
