<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Repository\ProductionOrderRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ProductionOrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderRepository extends ResourceRepository implements ProductionOrderRepositoryInterface
{
    use SubjectArgumentTrait;

    public function findScheduled(): array
    {
        return $this->findBy(['state' => POState::SCHEDULED], ['startAt' => 'ASC']);
    }

    public function findNotDoneBySubject(Reference|Subject|Identity $identity): array
    {
        $identity = $this->getIdentity($identity);

        $qb = $this->createQueryBuilder('o');

        return$qb
            ->andWhere($qb->expr()->eq('o.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('o.subjectIdentity.identifier', ':identifier'))
            ->andWhere($qb->expr()->neq('o.state', ':state'))
            ->orderBy('o.startAt', 'ASC')
            ->getQuery()
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
                'state'      => POState::DONE->value,
            ])
            ->getResult();
    }
}
