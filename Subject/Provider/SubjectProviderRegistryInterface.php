<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;

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
    public function addProvider(SubjectProviderInterface $provider): void;

    /**
     * Returns the provider by name or reference or subject.
     *
     * @param string|Reference|object $nameOrReferenceOrSubject
     *
     * @return SubjectProviderInterface|null
     */
    public function getProvider($nameOrReferenceOrSubject): ?SubjectProviderInterface;

    /**
     * Returns the provider supporting the item.
     *
     * @param Reference $reference
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderByReference(Reference $reference): ?SubjectProviderInterface;

    /**
     * Returns the provider supporting the subject.
     *
     * @param Subject $subject
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderBySubject(Subject $subject): ?SubjectProviderInterface;

    /**
     * Returns the provider by name.
     *
     * @param string $name
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderByName(string $name): ?SubjectProviderInterface;

    /**
     * Returns the providers.
     *
     * @return array|SubjectProviderInterface[]
     */
    public function getProviders(): array;
}
