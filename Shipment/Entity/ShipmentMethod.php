<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;

/**
 * Class ShipmentMethod
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethod extends AbstractMethod implements Shipment\ShipmentMethodInterface
{
    use TaxableTrait;

    protected ?string $platformName  = null;
    protected ?string $gatewayName   = null;
    protected array   $gatewayConfig = [];
    /** @var Collection|Shipment\ShipmentPriceInterface[] */
    protected Collection $prices;


    public function __construct()
    {
        parent::__construct();

        $this->prices = new ArrayCollection();
    }

    public function getPlatformName(): ?string
    {
        return $this->platformName;
    }

    public function setPlatformName(string $name): ShipmentMethodInterface
    {
        $this->platformName = $name;

        return $this;
    }

    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $name): ShipmentMethodInterface
    {
        $this->gatewayName = $name;

        return $this;
    }

    public function getGatewayConfig(): array
    {
        return $this->gatewayConfig;
    }

    public function setGatewayConfig(array $config): ShipmentMethodInterface
    {
        $this->gatewayConfig = $config;

        return $this;
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function hasPrices(): bool
    {
        return 0 < $this->prices->count();
    }

    public function hasPrice(Shipment\ShipmentPriceInterface $price): bool
    {
        return $this->prices->contains($price);
    }

    public function addPrice(Shipment\ShipmentPriceInterface $price): ShipmentMethodInterface
    {
        if ($this->hasPrice($price)) {
            return $this;
        }

        $this->prices->add($price);
        $price->setMethod($this);

        return $this;
    }

    public function removePrice(Shipment\ShipmentPriceInterface $price): ShipmentMethodInterface
    {
        if (!$this->hasPrice($price)) {
            return $this;
        }

        $this->prices->removeElement($price);
        $price->setMethod(null);

        return $this;
    }

    protected function validateMessageClass(MessageInterface $message): void
    {
        if (!$message instanceof ShipmentMessage) {
            throw new UnexpectedTypeException($message, ShipmentMessage::class);
        }
    }

    protected function getTranslationClass(): string
    {
        return ShipmentMethodTranslation::class;
    }
}
