<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Trait SubjectIdentityTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @see     SubjectReferenceInterface
 */
trait SubjectReferenceTrait
{
    /**
     * @var SubjectIdentity
     */
    protected $subjectIdentity;


    /**
     * Initializes the subject identity.
     */
    protected function initializeSubjectIdentity(): void
    {
        $this->subjectIdentity = new SubjectIdentity();
    }

    /**
     * Returns whether or not the subject identity is set.
     *
     * @return bool
     * @see SubjectIdentity::hasIdentity()
     *
     */
    public function hasSubjectIdentity(): bool
    {
        return $this->subjectIdentity->hasIdentity();
    }

    /**
     * Returns the subject identity.
     *
     * @return SubjectIdentity
     *
     * @internal
     */
    public function getSubjectIdentity(): SubjectIdentity
    {
        return $this->subjectIdentity;
    }

    /**
     * Sets the subject identity.
     *
     * @param SubjectIdentity $identity
     *
     * @return $this|SubjectReferenceInterface
     *
     * @internal
     */
    public function setSubjectIdentity(SubjectIdentity $identity): SubjectReferenceInterface
    {
        $this->subjectIdentity = $identity;

        return $this;
    }

    /**
     * Clears the subject identity.
     *
     * @return $this|SubjectReferenceInterface
     *
     * @internal
     */
    public function clearSubjectIdentity(): SubjectReferenceInterface
    {
        $this->subjectIdentity->clear();

        return $this;
    }
}
