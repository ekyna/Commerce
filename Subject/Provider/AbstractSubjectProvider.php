<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;

/**
 * Class AbstractSubjectProvider
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSubjectProvider implements SubjectProviderInterface
{
    protected SubjectRepositoryInterface $repository;

    public function __construct(SubjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function assign(Reference $reference, Subject $subject): SubjectProviderInterface
    {
        return $this->transform($subject, $reference->getSubjectIdentity());
    }

    public function resolve(Reference $reference): Subject
    {
        return $this->reverseTransform($reference->getSubjectIdentity());
    }

    public function transform(Subject $subject, Identity $identity): SubjectProviderInterface
    {
        $this->assertSupportsSubject($subject);

        if ($subject === $identity->getSubject()) {
            return $this;
        }

        $identity
            ->setProvider(static::getName())
            ->setIdentifier($subject->getId())
            ->setSubject($subject);

        return $this;
    }

    public function reverseTransform(Identity $identity): Subject
    {
        $this->assertSupportsIdentity($identity);

        $identifier = $identity->getIdentifier();

        if (null !== $subject = $identity->getSubject()) {
            $class = $this->getSubjectClass();
            if (!$subject instanceof $class) {
                throw new SubjectException('Failed to transform subject identity.');
            }

            if ($subject->getId() !== $identifier) {
                throw new SubjectException('Failed to transform subject identity.');
            }

            return $subject;
        }

        if (null === $subject = $this->repository->find($identifier)) {
            throw new SubjectException('Failed to transform subject identity.');
        }

        $identity->setSubject($subject);

        return $subject;
    }

    public function supportsReference(Reference $reference): bool
    {
        return $reference->getSubjectIdentity()->getProvider() === static::getName();
    }

    public function supportsSubject(Subject $subject): bool
    {
        $class = $this->getSubjectClass();

        return $subject instanceof $class;
    }

    public function getRepository(): SubjectRepositoryInterface
    {
        return $this->repository;
    }

    public function getSubjectClass(): string
    {
        return $this->repository->getClassName();
    }

    public function getSearchActionAndParameters(string $context): array
    {
        return [];
    }

    /**
     * Asserts that the subject reference is supported.
     *
     * @throws SubjectException
     */
    protected function assertSupportsSubject(Subject $subject): void
    {
        if ($this->supportsSubject($subject)) {
            return;
        }

        throw new SubjectException('Unsupported subject.');
    }

    /**
     * Asserts that the subject identity is supported.
     *
     * @throws SubjectException
     */
    protected function assertSupportsIdentity(Identity $identity): void
    {
        if ($identity->getProvider() === static::getName()) {
            return;
        }

        throw new SubjectException('Unsupported subject identity.');
    }
}
