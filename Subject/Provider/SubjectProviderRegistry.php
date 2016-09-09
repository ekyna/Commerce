<?php

namespace Ekyna\Component\Commerce\Subject\Provider;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Interface SubjectProviderInterface
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectProviderRegistry implements SubjectProviderRegistryInterface
{
    /**
     * @var array|SubjectProviderInterface[]
     */
    protected $providers;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->providers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider(SubjectProviderInterface $provider)
    {
        if (array_key_exists($provider->getName(), $this->providers)) {
            throw new \RuntimeException(sprintf('Subject provider "%s" is already registered.', $provider->getName()));
        }

        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * Returns the provider by name or sale item.
     *
     * @param string|SaleItemInterface $nameOrItem
     *
     * @return SubjectProviderInterface|mixed
     */
    public function getProvider($nameOrItem)
    {
        if ($nameOrItem instanceof SaleItemInterface) {
            $data = $nameOrItem->getSubjectData();
            if (!array_key_exists('provider', $data)) {
                throw new InvalidArgumentException("Unexpected sale item data ('provider' key is missing).");
            }
            $name = $data['provider'];
        } else {
            $name = $nameOrItem;
        }

        if (array_key_exists($name, $this->providers)) {
            return $this->providers[$name];
        }

        throw new InvalidArgumentException("Provider '$name' not found.");
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
