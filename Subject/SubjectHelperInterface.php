<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface as SubjectProvider;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Interface SubjectHelperInterface
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectHelperInterface
{
    /**
     * Returns whether or the subject relative has subject.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return bool
     */
    public function hasSubject(SubjectRelativeInterface $relative): bool;

    /**
     * Resolves the subject from the relative.
     *
     * @param SubjectRelativeInterface $relative
     * @param bool                     $throw
     *
     * @return SubjectInterface
     *
     * @throws SubjectException
     */
    public function resolve(SubjectRelativeInterface $relative, $throw = true): ?SubjectInterface;

    /**
     * Assigns the subject to the relative.
     *
     * @param SubjectRelativeInterface $relative
     * @param SubjectInterface         $subject
     *
     * @return SubjectProvider
     */
    public function assign(SubjectRelativeInterface $relative, SubjectInterface $subject): SubjectProvider;

    /**
     * Finds the subject by its provider and identifier.
     *
     * @param string $provider
     * @param string $identifier
     *
     * @return Model\SubjectInterface|null
     */
    public function find($provider, $identifier): ?SubjectInterface;

    /**
     * Returns the subject 'add to cart' url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generateAddToCartUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject public url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generatePublicUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject image url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generateImageUrl($subject, bool $path = true): ?string;

    /**
     * Returns the subject private url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generatePrivateUrl($subject, bool $path = true): ?string;
}
