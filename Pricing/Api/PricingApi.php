<?php

namespace Ekyna\Component\Commerce\Pricing\Api;

/**
 * Class Api
 * @package Ekyna\Component\Commerce\Tax
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingApi implements PricingApiInterface
{
    const PROVIDER_TAG = 'ekyna_commerce.pricing.api_provider';

    /**
     * @var array
     */
    private $providers;


    /**
     * Constructor.
     *
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function validateVatNumber($vatNumber)
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
