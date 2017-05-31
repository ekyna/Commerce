<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Interface SubjectRelativeInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see SubjectRelativeTrait
 */
interface SubjectRelativeInterface
{
    /**
     * Returns whether or not the subject identity is set.
     *
     * @see SubjectIdentity::hasIdentity()
     *
     * @return bool
     */
    public function hasSubjectIdentity();

    /**
     * Returns the subject identity.
     *
     * @return SubjectIdentity
     */
    public function getSubjectIdentity();

    /**
     * Clears the subject identity.
     *
     * @return $this|SubjectRelativeInterface
     */
    public function clearSubjectIdentity();
}
