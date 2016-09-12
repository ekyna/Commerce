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
     * @inheritdoc
     */
    public function getProvider($nameOrItemOrSubject)
    {
        if ($nameOrItemOrSubject instanceof SaleItemInterface) {
            return $this->getProviderByItem($nameOrItemOrSubject);
        } elseif (is_object($nameOrItemOrSubject)) {
            return $this->getProviderBySubject($nameOrItemOrSubject);
        } elseif (is_string($nameOrItemOrSubject)) {
            return $this->getProviderByName($nameOrItemOrSubject);
        }

        throw new InvalidArgumentException("Failed to resolve provider.");
    }

    /**
     * @inheritdoc
     */
    public function getProviderByItem(SaleItemInterface $item)
    {
        if (null !== $subject = $item->getSubject()) {
            return $this->getProviderBySubject($subject);
        }

        foreach ($this->providers as $provider) {
            if ($provider->supportsItem($item)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getProviderBySubject($subject)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportsSubject($subject)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getProviderByName($name)
    {
        if (array_key_exists($name, $this->providers)) {
            return $this->providers[$name];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
