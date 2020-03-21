<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;

/**
 * Class ResolvedTaxesCache
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResolvedTaxesCache
{
    /**
     * @var array
     */
    private $taxes;


    /**
     * Returns the taxes for the given tax group, country and business flag.
     *
     * @param TaxGroupInterface $taxGroup
     * @param CountryInterface  $country
     * @param bool              $business
     *
     * @return TaxInterface[]|null
     */
    public function get(TaxGroupInterface $taxGroup, CountryInterface $country, $business = false): ?array
    {
        $key = $this->buildKey($taxGroup, $country, $business);

        if (isset($this->taxes[$key])) {
            return $this->taxes[$key];
        }

        return null;
    }

    /**
     * Caches the taxes for the given tax group, country and business flag.
     *
     * @param TaxGroupInterface $taxGroup
     * @param CountryInterface  $country
     * @param bool              $business
     * @param array             $taxes
     */
    public function set(TaxGroupInterface $taxGroup, CountryInterface $country, bool $business, array $taxes): void
    {
        $key = $this->buildKey($taxGroup, $country, $business);

        $this->taxes[$key] = $taxes;
    }

    /**
     * Builds the cache key.
     *
     * @param TaxGroupInterface $taxGroup
     * @param CountryInterface  $country
     * @param bool              $business
     *
     * @return string
     */
    private function buildKey(TaxGroupInterface $taxGroup, CountryInterface $country, bool $business = false): string
    {
        return sprintf('%s-%s-%s', $taxGroup->getId(), $country->getId(), (int)$business);
    }
}
