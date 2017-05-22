<?php

namespace Ekyna\Component\Commerce\Customer\Validator;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class VatNumberValidator
 * @package Ekyna\Component\Commerce\Customer\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatNumberValidator implements VatNumberValidatorInterface
{
    /**
     * @var Provider\ProviderInterface[]
     */
    private $providers;


    /**
     * Constructor.
     *
     * @param Provider\ProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            if (!$provider instanceof Provider\ProviderInterface) {
                throw new InvalidArgumentException("Expected instance of " . Provider\ProviderInterface::class);
            }
        }

        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function validate($vatNumber)
    {
        foreach ($this->providers as $provider) {
            if (null !== $result = $provider->validate($vatNumber)) {
                return $result;
            }
        }

        return null;
    }
}
