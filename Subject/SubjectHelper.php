<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SubjectHelper
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper implements SubjectHelperInterface
{
    protected SubjectProviderRegistryInterface $registry;
    protected EventDispatcherInterface $eventDispatcher;
    protected Features $features;

    public function __construct(
        SubjectProviderRegistryInterface $registry,
        EventDispatcherInterface $eventDispatcher,
        Features $features
    ) {
        $this->registry = $registry;
        $this->eventDispatcher = $eventDispatcher;
        $this->features = $features;
    }

    public function hasSubject(SubjectReferenceInterface $reference): bool
    {
        return null !== $this->resolve($reference, false);
    }

    public function resolve(SubjectReferenceInterface $reference, bool $throw = true): ?SubjectInterface
    {
        if (!$reference->getSubjectIdentity()->hasIdentity()) {
            return null;
        }

        try {
            return $this->getProvider($reference)->resolve($reference);
        } catch (SubjectException $e) {
            if ($throw) {
                throw $e;
            }
        }

        return null;
    }

    public function assign(SubjectReferenceInterface $reference, SubjectInterface $subject): SubjectProviderInterface
    {
        return $this->getProvider($subject)->assign($reference, $subject);
    }

    public function find(string $provider, int $identifier): ?SubjectInterface
    {
        return $this->getProvider($provider)->getRepository()->find($identifier);
    }

    public function sync(SubjectRelativeInterface $relative): bool
    {
        if (!$this->hasSubject($relative)) {
            return false;
        }

        if (!$subject = $this->resolve($relative, false)) {
            return false;
        }

        $changed = false;

        $designation = (string) $subject;
        if ($designation !== $relative->getDesignation()) {
            $relative->setDesignation($designation);
            $changed = true;
        }

        if ($subject->getReference() !== $relative->getReference()) {
            $relative->setReference($subject->getReference());
            $changed = true;
        }

        if (!$subject->getNetPrice()->equals($relative->getNetPrice())) {
            $relative->setNetPrice(clone $subject->getNetPrice());
            $changed = true;
        }

        if (!$subject->getWeight()->equals($relative->getWeight())) {
            $relative->setWeight(clone $subject->getWeight());
            $changed = true;
        }

        if ($subject->getTaxGroup() !== $relative->getTaxGroup()) {
            $relative->setTaxGroup($subject->getTaxGroup());
            $changed = true;
        }

        return $changed;
    }

    /**
     * Returns the provider by name or supporting the given relative or subject.
     *
     * @param string|SubjectRelativeInterface|object $nameOrRelativeOrSubject
     *
     * @throws SubjectException
     */
    protected function getProvider($nameOrRelativeOrSubject): SubjectProviderInterface
    {
        if (null === $provider = $this->registry->getProvider($nameOrRelativeOrSubject)) {
            throw new SubjectException('Failed to get provider.');
        }

        return $provider;
    }
}
