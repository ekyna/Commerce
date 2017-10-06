<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\SubjectException;
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
     */
    public function resolve(SubjectRelativeInterface $relative, $throw = true);

    /**
     * Assigns the subject to the relative.
     *
     * @param SubjectRelativeInterface $relative
     * @param mixed                    $subject
     *
     * @return Provider\SubjectProviderInterface
     */
    public function assign(SubjectRelativeInterface $relative, $subject);
}
