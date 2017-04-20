<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;

/**
 * Class ResolvedShipmentPrice
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResolvedShipmentPrice
{
    private ShipmentMethodInterface $method;
    private Decimal                 $weight;
    private Decimal                 $price;
    /** @var array<Decimal> */
    private array $taxes;

    public function __construct(ShipmentMethodInterface $method, Decimal $weight, Decimal $price = null)
    {
        $this->method = $method;
        $this->weight = $weight;
        $this->price = $price ?: new Decimal(0);
        $this->taxes = [];
    }

    public function getMethod(): ShipmentMethodInterface
    {
        return $this->method;
    }

    public function getWeight(): Decimal
    {
        return $this->weight;
    }

    public function setPrice(Decimal $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice(): Decimal
    {
        return $this->price;
    }

    /**
     * @param array<Decimal> $taxes
     */
    public function setTaxes(array $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    /**
     * @return array<Decimal>
     */
    public function getTaxes(): array
    {
        return $this->taxes;
    }

    public function isFree(): bool
    {
        return $this->price->equals(0);
    }
}
