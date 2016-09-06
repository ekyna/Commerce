<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

/**
 * Interface SubjectProviderRegistryInterface
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectProviderRegistryInterface
{
    /**
     * Adds the subject provider.
     *
     * @param SubjectProviderInterface $provider
     */
    public function addProvider(SubjectProviderInterface $provider);

    /**
     * Returns the provider by name.
     *
     * @param string $name
     *
     * @return SubjectProviderInterface|mixed
     */
    public function getProvider($name);

    /**
     * Returns the providers.
     *
     * @return array|SubjectProviderInterface[]
     */
    public function getProviders();
}
