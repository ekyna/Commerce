<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface as SubjectProvider;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Interface SubjectHelperInterface
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectHelperInterface
{
    /**
     * Returns whether or the subject reference has subject.
     *
     * @param SubjectReferenceInterface $reference
     *
     * @return bool
     */
    public function hasSubject(SubjectReferenceInterface $reference): bool;

    /**
     * Resolves the subject from the reference.
     *
     * @param SubjectReferenceInterface $reference
     * @param bool                     $throw
     *
     * @return SubjectInterface
     *
     * @throws SubjectException
     */
    public function resolve(SubjectReferenceInterface $reference, bool $throw = true): ?SubjectInterface;

    /**
     * Assigns the subject to the reference.
     *
     * @param SubjectReferenceInterface $reference
     * @param SubjectInterface         $subject
     *
     * @return SubjectProvider
     */
    public function assign(SubjectReferenceInterface $reference, SubjectInterface $subject): SubjectProvider;

    /**
     * Finds the subject by its provider and identifier.
     *
     * @param string $provider
     * @param string $identifier
     *
     * @return Model\SubjectInterface|null
     */
    public function find(string $provider, string $identifier): ?SubjectInterface;

    /**
     * Syncs the relative with it's subject data.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return bool Whether the relative has been changed.
     */
    public function sync(SubjectRelativeInterface $relative): bool;

    /**
     * Returns the subject 'add to cart' url.
     *
     * @param SubjectReferenceInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generateAddToCartUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject public url.
     *
     * @param SubjectReferenceInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generatePublicUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject image url.
     *
     * @param SubjectReferenceInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generateImageUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject private url.
     *
     * @param SubjectReferenceInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generatePrivateUrl($subject, bool $path = true): ?string;
}
