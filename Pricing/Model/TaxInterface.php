<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\AdjustmentDataInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface TaxInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxInterface extends AdjustmentDataInterface, ResourceInterface
{
    public function getCode(): ?string;

    public function setCode(string $code): TaxInterface;

    public function getName(): ?string;

    public function setName(string $name): TaxInterface;

    public function getRate(): Decimal;

    public function setRate(Decimal $rate): TaxInterface;

    public function getCountry(): ?CountryInterface;

    public function setCountry(CountryInterface $country): TaxInterface;

    public function getState(): ?StateInterface;

    public function setState(?StateInterface $state): TaxInterface;
}
