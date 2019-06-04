<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Trait SubjectRelativeTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see     SubjectRelativeInterface
 */
trait SubjectRelativeTrait
{
    /**
     * @var SubjectIdentity
     */
    protected $subjectIdentity;


    /**
     * Initializes the subject identity.
     */
    protected function initializeSubjectIdentity()
    {
        $this->subjectIdentity = new SubjectIdentity();
    }

    /**
     * Returns whether or not the subject identity is set.
     *
     * @see SubjectIdentity::hasIdentity()
     *
     * @return bool
     */
    public function hasSubjectIdentity()
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
    public function getSubjectIdentity()
    {
        return $this->subjectIdentity;
    }

    /**
     * Sets the subject identity.
     *
     * @param SubjectIdentity $identity
     *
     * @return $this|SubjectRelativeInterface
     *
     * @internal
     */
    public function setSubjectIdentity(SubjectIdentity $identity)
    {
        $this->subjectIdentity = $identity;

        return $this;
    }

    /**
     * Clears the subject identity.
     *
     * @return $this|SubjectRelativeInterface
     *
     * @internal
     */
    public function clearSubjectIdentity()
    {
        $this->subjectIdentity->clear();

        return $this;
    }
}
