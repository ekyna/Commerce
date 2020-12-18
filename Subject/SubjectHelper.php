<?php

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
    /**
     * @var SubjectProviderRegistryInterface
     */
    protected $registry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Features
     */
    protected $features;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     * @param EventDispatcherInterface         $eventDispatcher
     * @param Features                         $features
     */
    public function __construct(
        SubjectProviderRegistryInterface $registry,
        EventDispatcherInterface $eventDispatcher,
        Features $features
    ) {
        $this->registry = $registry;
        $this->eventDispatcher = $eventDispatcher;
        $this->features = $features;
    }

    /**
     * @inheritdoc
     */
    public function hasSubject(SubjectReferenceInterface $reference): bool
    {
        return null !== $this->resolve($reference, false);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function assign(SubjectReferenceInterface $reference, $subject): SubjectProviderInterface
    {
        return $this->getProvider($subject)->assign($reference, $subject);
    }

    /**
     * @inheritdoc
     */
    public function find(string $provider, string $identifier): ?SubjectInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getProvider($provider)->getRepository()->find($identifier);
    }

    /**
     * @inheritDoc
     */
    public function sync(SubjectRelativeInterface $relative): bool
    {
        if (!$this->hasSubject($relative)) {
            return false;
        }

        if (!$subject = $this->resolve($relative, false)) {
            return false;
        }

        $changed = false;

        if ($subject->getDesignation() !== $relative->getDesignation()) {
            $relative->setDesignation($subject->getDesignation());
            $changed = true;
        }

        if ($subject->getReference() !== $relative->getReference()) {
            $relative->setReference($subject->getReference());
            $changed = true;
        }

        if ($subject->getNetPrice() !== $relative->getNetPrice()) {
            $relative->setNetPrice($subject->getNetPrice());
            $changed = true;
        }

        if ($subject->getWeight() !== $relative->getWeight()) {
            $relative->setWeight($subject->getWeight());
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
     * @return SubjectProviderInterface
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
