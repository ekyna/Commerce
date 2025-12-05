<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class BillOfMaterialsRepository
 * @package Ekyna\Component\Commerce\Manufacture\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsRepository extends ResourceRepository implements BillOfMaterialsRepositoryInterface
{
    use SubjectArgumentTrait;

    public function findNewVersion(BillOfMaterialsInterface $bom): ?BillOfMaterialsInterface
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->andWhere($qb->expr()->eq('b.number', ':number'))
            ->andWhere($qb->expr()->eq('b.state', ':state'))
            ->andWhere($qb->expr()->neq('b', ':bom'))
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->setParameter('number', $bom->getNumber())
            ->setParameter('state', BOMState::VALIDATED)
            ->setParameter('bom', $bom)
            ->getOneOrNullResult();
    }

    public function findOneValidatedBySubject(Reference|Subject|Identity $identity): ?BillOfMaterialsInterface
    {
        $identity = $this->getIdentity($identity);

        $qb = $this->createQueryBuilder('b');

        return $qb
            ->andWhere($qb->expr()->eq('b.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('b.subjectIdentity.identifier', ':identifier'))
            ->andWhere($qb->expr()->eq('b.state', ':state'))
            ->getQuery()
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
                'state'      => BOMState::VALIDATED,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findBySubject(Reference|Subject|Identity $identity): array
    {
        $identity = $this->getIdentity($identity);

        $qb = $this->createQueryBuilder('b');

        return $qb
            ->andWhere($qb->expr()->eq('b.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('b.subjectIdentity.identifier', ':identifier'))
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
            ])
            ->getResult();
    }

    public function findOneValidatedByComponentWithSubject(
        Reference|Subject|Identity $identity
    ): ?BillOfMaterialsInterface {
        $identity = $this->getIdentity($identity);

        $qb = $this->createQueryBuilder('b');

        return $qb
            ->join('b.components', 'c')
            ->andWhere($qb->expr()->eq('c.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('c.subjectIdentity.identifier', ':identifier'))
            ->andWhere($qb->expr()->eq('b.state', ':state'))
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
                'state'      => BOMState::VALIDATED,
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByComponentWithSubject(Reference|Subject|Identity $identity): array
    {
        $identity = $this->getIdentity($identity);

        $qb = $this->createQueryBuilder('b');

        return$qb
            ->join('b.components', 'c')
            ->andWhere($qb->expr()->eq('c.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('c.subjectIdentity.identifier', ':identifier'))
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findValidatedByComponentWithSubject(Reference|Subject|Identity $identity): array
    {
        $identity = $this->getIdentity($identity);

        $qb = $this->createQueryBuilder('b');

        return$qb
            ->join('b.components', 'c')
            ->andWhere($qb->expr()->eq('c.subjectIdentity.provider', ':provider'))
            ->andWhere($qb->expr()->eq('c.subjectIdentity.identifier', ':identifier'))
            ->andWhere($qb->expr()->eq('b.state', ':state'))
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
                'state'      => BOMState::VALIDATED,
            ])
            ->getResult();
    }
}
