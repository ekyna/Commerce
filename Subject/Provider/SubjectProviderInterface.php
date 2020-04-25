<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface as Relative;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;

/**
 * Interface SubjectProviderInterface
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectProviderInterface
{
    const CONTEXT_ITEM     = 'item';     // Sale item subject
    const CONTEXT_SALE     = 'sale';     // Sale item search
    const CONTEXT_ACCOUNT  = 'account';  // Sale item search from customer account
    const CONTEXT_SUPPLIER = 'supplier'; // Supplier item subject


    /**
     * Sets the subject int the relative.
     *
     * This method must set the relative subject fields (provider and identifier) for next resolutions.
     *
     * @param Relative $relative
     * @param Subject  $subject
     *
     * @return SubjectProviderInterface
     */
    public function assign(Relative $relative, Subject $subject): SubjectProviderInterface;

    /**
     * Returns the subject from the relative.
     *
     * This method should set the subject into the relative for future resolutions.
     *
     * @param Relative $relative
     *
     * @return Subject
     *
     * @throws SubjectException
     */
    public function resolve(Relative $relative): Subject;

    /**
     * Transforms the subject into the identity.
     *
     * This method must set the subject identity fields (provider and identifier) and
     * set the subject into the identity prior to next reverse transformations.
     *
     * @param Subject  $subject
     * @param Identity $identity
     *
     * @return SubjectProviderInterface
     */
    public function transform(Subject $subject, Identity $identity): SubjectProviderInterface;

    /**
     * Reverse transform the identity into the subject.
     *
     * This method must set the subject into the identity prior to next reverse transformations.
     *
     * @param Identity $identity
     *
     * @return Subject
     *
     * @throws SubjectException
     */
    public function reverseTransform(Identity $identity): Subject;

    /**
     * Returns whether the resolver supports the relative or not.
     *
     * @param Relative $relative
     *
     * @return bool
     */
    public function supportsRelative(Relative $relative): bool;

    /**
     * Returns whether the provider supports the subject or not.
     *
     * @param Subject $subject
     *
     * @return bool
     */
    public function supportsSubject(Subject $subject): bool;

    /**
     * Returns the subject repository.
     *
     * @return SubjectRepositoryInterface
     */
    public function getRepository(): SubjectRepositoryInterface;

    /**
     * Returns the subject class.
     *
     * @return string
     */
    public function getSubjectClass(): string;

    /**
     * Returns the search url for the given context.
     *
     * [
     *     'route'      => <string>,
     *     'parameters' => <array>,
     * ]
     *
     * @param string $context
     *
     * @return array
     */
    public function getSearchRouteAndParameters(string $context): array;

    /**
     * Returns the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the subject type label.
     *
     * @return string
     */
    public function getLabel(): string;
}
