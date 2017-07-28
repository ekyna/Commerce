<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

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
     * Returns the provider by name or relative or subject.
     *
     * @param string|SubjectRelativeInterface|object $nameOrRelativeOrSubject
     *
     * @return SubjectProviderInterface|null
     */
    public function getProvider($nameOrRelativeOrSubject);

    /**
     * Returns the provider supporting the item.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderByRelative(SubjectRelativeInterface $relative);

    /**
     * Returns the provider supporting the subject.
     *
     * @param SubjectInterface $subject
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderBySubject(SubjectInterface $subject);

    /**
     * Returns the provider by name.
     *
     * @param string $name
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderByName($name);

    /**
     * Returns the providers.
     *
     * @return array|SubjectProviderInterface[]
     */
    public function getProviders();
}
