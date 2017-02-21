<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

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
    public function getProvider($nameOrRelativeOrSubject)
    {
        if ($nameOrRelativeOrSubject instanceof SubjectRelativeInterface) {
            return $this->getProviderByRelative($nameOrRelativeOrSubject);
        } elseif (is_object($nameOrRelativeOrSubject)) {
            return $this->getProviderBySubject($nameOrRelativeOrSubject);
        } elseif (is_string($nameOrRelativeOrSubject)) {
            return $this->getProviderByName($nameOrRelativeOrSubject);
        }

        throw new InvalidArgumentException("Failed to resolve provider.");
    }

    /**
     * @inheritdoc
     */
    public function getProviderByRelative(SubjectRelativeInterface $relative)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if (!empty($name = $relative->getSubjectIdentity()->getProvider())) {
            return $this->getProviderByName($name);
        }

        foreach ($this->providers as $provider) {
            if ($provider->supportsRelative($relative)) {
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
     *
     * @TODO Remove : use providers
     */
    public function resolveRelativeSubject(SubjectRelativeInterface $relative)
    {
        if (null !== $provider = $this->getProviderByRelative($relative)) {
            return $provider->resolve($relative);
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
