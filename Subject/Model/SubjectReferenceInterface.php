<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Interface SubjectReferenceInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectReferenceInterface
{
    /**
     * Returns whether or not the subject identity is set.
     *
     * @return bool
     *
     * @see SubjectIdentity::hasIdentity()
     */
    public function hasSubjectIdentity(): bool;

    /**
     * Returns the subject identity.
     *
     * @return SubjectIdentity
     */
    public function getSubjectIdentity(): SubjectIdentity;

    /**
     * Sets the subject identity.
     *
     * @param SubjectIdentity $identity
     *
     * @return $this|SubjectReferenceInterface
     *
     * @internal
     */
    public function setSubjectIdentity(SubjectIdentity $identity): SubjectReferenceInterface;

    /**
     * Clears the subject identity.
     *
     * @return $this|SubjectReferenceInterface
     */
    public function clearSubjectIdentity(): SubjectReferenceInterface;
}
