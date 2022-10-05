<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Trait RemoveBySubjectIdentityTrait
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 * @internal
 */
trait RemoveBySubjectIdentityTrait
{
    protected function doRemoveBySubjectIdentity(EntityManagerInterface $manager, SubjectIdentity $identity): void
    {
        $qb = $manager->createQueryBuilder();
        $qb
            ->from($this->getClassName(), 'o')
            ->select('o')
            ->andWhere($qb->expr()->eq('o.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('o.subjectIdentity.identifier', ':identifier'))
            ->setMaxResults(20)
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
            ]);

        $items = $qb->getQuery()->getResult();

        while (!empty($items)) {
            foreach ($items as $item) {
                if (null !== $r = $item->getRoot()) {
                    $item = $r;
                }

                $manager->remove($item);
            }

            $manager->flush();
            $manager->clear();

            $items = $qb->getQuery()->getResult();
        }
    }
}
