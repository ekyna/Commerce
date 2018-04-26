<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

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
    public function hasSubject(SubjectRelativeInterface $relative);

    /**
     * Resolves the subject from the relative.
     *
     * @param SubjectRelativeInterface $relative
     * @param bool                     $throw
     *
     * @throws SubjectException
     *
     * @return object
     * @TODO @return SubjectInterface
     */
    public function resolve(SubjectRelativeInterface $relative, $throw = true);

    /**
     * Assigns the subject to the relative.
     *
     * @param SubjectRelativeInterface $relative
     * @param mixed                    $subject
     * @TODO @param SubjectInterface $subject
     *
     * @return Provider\SubjectProviderInterface
     */
    public function assign(SubjectRelativeInterface $relative, $subject);

    /**
     * Finds the subject by its provider and identifier.
     *
     * @param string $provider
     * @param string $identifier
     *
     * @return Model\SubjectInterface|null
     */
    public function find($provider, $identifier);

    /**
     * Returns the subject 'add to cart' url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generateAddToCartUrl($subject, $path = true);

    /**
     * Returns the subject public url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generatePublicUrl($subject, $path = true);

    /**
     * Returns the subject private url.
     *
     * @param SubjectRelativeInterface|SubjectInterface $subject
     * @param bool                                      $path
     *
     * @return null|string
     */
    public function generatePrivateUrl($subject, $path = true);
}
