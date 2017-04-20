<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Api;

/**
 * Class Api
 * @package Ekyna\Component\Commerce\Tax
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingApi implements PricingApiInterface
{
    public const PROVIDER_TAG = 'ekyna_commerce.pricing.api_provider';

    private array $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function validateVatNumber(string $vatNumber): ?VatNumberResult
    {
        foreach ($this->providers as $provider) {
            if ($provider instanceof Provider\VatNumberValidatorInterface) {
                if (null !== $result = $provider->validateVatNumber($vatNumber)) {
                    return $result;
                }
            }
        }

        return null;
    }
}
