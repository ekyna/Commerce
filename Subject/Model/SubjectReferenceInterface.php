<?php

declare(strict_types=1);

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
     * @see SubjectIdentity::hasIdentity()
     */
    public function hasSubjectIdentity(): bool;

    public function getSubjectIdentity(): SubjectIdentity;

    /**
     * @internal
     */
    public function setSubjectIdentity(SubjectIdentity $identity): SubjectReferenceInterface;

    public function clearSubjectIdentity(): SubjectReferenceInterface;
}
