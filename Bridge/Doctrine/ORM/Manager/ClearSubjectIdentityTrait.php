<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Trait ClearSubjectIdentityTrait
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ClearSubjectIdentityTrait
{
    protected function doClearSubjectIdentity(EntityManagerInterface $manager, SubjectIdentity $identity): void
    {
        $qb = $manager->createQueryBuilder();
        $qb
            ->update($this->getClassName(), 'o')
            ->set('o.subjectIdentity.provider', ':null')
            ->set('o.subjectIdentity.identifier', ':null')
            ->andWhere($qb->expr()->eq('o.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('o.subjectIdentity.identifier', ':identifier'))
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
                'null'       => null,
            ])
            ->getQuery()
            ->execute();
    }
}
