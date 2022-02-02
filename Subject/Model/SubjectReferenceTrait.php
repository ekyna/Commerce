<?php

declare(strict_types=1);

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
    protected SubjectIdentity $subjectIdentity;

    protected function initializeSubjectIdentity(): void
    {
        $this->subjectIdentity = new SubjectIdentity();
    }

    public function hasSubjectIdentity(): bool
    {
        return $this->subjectIdentity->hasIdentity();
    }

    public function getSubjectIdentity(): SubjectIdentity
    {
        return $this->subjectIdentity;
    }

    /**
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
