<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;

/**
 * Interface SubjectProviderInterface
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectProviderInterface
{
    public const CONTEXT_ITEM     = 'item';         // Sale item subject
    public const CONTEXT_SALE     = 'sale';         // Sale item search
    public const CONTEXT_ACCOUNT  = 'account';   // Sale item search from customer account
    public const CONTEXT_SUPPLIER = 'supplier'; // Supplier item subject


    /**
     * Assigns the subject to the reference.
     *
     * This method must set the reference subject fields (provider and identifier) for next resolutions.
     *
     * @param Reference $reference
     * @param Subject   $subject
     *
     * @return SubjectProviderInterface
     */
    public function assign(Reference $reference, Subject $subject): SubjectProviderInterface;

    /**
     * Returns the subject from the reference.
     *
     * This method should set the subject into the reference for future resolutions.
     *
     * @param Reference $reference
     *
     * @return Subject
     *
     * @throws SubjectException
     */
    public function resolve(Reference $reference): Subject;

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
     * Returns whether the resolver supports the reference or not.
     *
     * @param Reference $reference
     *
     * @return bool
     */
    public function supportsReference(Reference $reference): bool;

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
