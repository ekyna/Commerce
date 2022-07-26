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

/**
 * Class SubjectHelper
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper implements SubjectHelperInterface
{
    public function __construct(
        protected readonly SubjectProviderRegistryInterface $registry,
        protected readonly Features                         $features
    ) {
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
        } catch (SubjectException $exception) {
            if ($throw) {
                throw $exception;
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
