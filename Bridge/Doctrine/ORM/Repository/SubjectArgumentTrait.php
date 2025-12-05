<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;

/**
 * Trait SubjectArgumentTrait
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait SubjectArgumentTrait
{
    protected function getIdentity(Reference|Subject|Identity $identity): Identity
    {
        if ($identity instanceof Reference) {
            return $identity->getSubjectIdentity();
        }

        if ($identity instanceof Subject) {
            return Identity::fromSubject($identity);
        }

        return $identity;
    }
}
