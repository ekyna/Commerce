<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;

/**
 * Interface SubjectProviderInterface
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectProviderRegistry implements SubjectProviderRegistryInterface
{
    /** @var array<SubjectProviderInterface> */
    protected array $providers = [];

    /**
     * @inheritDoc
     */
    public function addProvider(SubjectProviderInterface $provider): void
    {
        if (array_key_exists($name = $provider::getName(), $this->providers)) {
            throw new RuntimeException(sprintf('Subject provider "%s" is already registered.', $name));
        }

        $this->providers[$name] = $provider;
    }

    /**
     * @inheritDoc
     */
    public function getProvider($nameOrReferenceOrSubject): ?SubjectProviderInterface
    {
        if ($nameOrReferenceOrSubject instanceof Reference) {
            return $this->getProviderByReference($nameOrReferenceOrSubject);
        } elseif ($nameOrReferenceOrSubject instanceof Subject) {
            return $this->getProviderBySubject($nameOrReferenceOrSubject);
        } elseif (is_string($nameOrReferenceOrSubject)) {
            return $this->getProviderByName($nameOrReferenceOrSubject);
        }

        throw new UnexpectedTypeException($nameOrReferenceOrSubject, [
            Reference::class,
            Subject::class,
            'string'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getProviderByReference(Reference $reference): ?SubjectProviderInterface
    {
        if (!empty($name = $reference->getSubjectIdentity()->getProvider())) {
            return $this->getProviderByName($name);
        }

        foreach ($this->providers as $provider) {
            if ($provider->supportsReference($reference)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getProviderBySubject(Subject $subject): ?SubjectProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportsSubject($subject)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getProviderByName(string $name): ?SubjectProviderInterface
    {
        if (array_key_exists($name, $this->providers)) {
            return $this->providers[$name];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
