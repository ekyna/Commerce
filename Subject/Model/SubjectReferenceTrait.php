<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
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

    public function copySubjectIdentity(
        SubjectReferenceInterface $source,
        bool                      $allowEmpty = true,
        bool                      $allowChange = true,
    ): bool {
        $sourceSI = $source->getSubjectIdentity();
        if (!$sourceSI->hasIdentity() && !$allowEmpty) {
            throw new InvalidArgumentException(
                'Subject identity is not set.'
            );
        }

        if ($this->subjectIdentity->equals($sourceSI)) {
            return false;
        }

        if ($this->subjectIdentity->hasIdentity() && !$allowChange) {
            throw new LogicException(
                'Forbidden subject identity change'
            );
        }

        $this->subjectIdentity->copy($sourceSI);

        return true;
    }
}
