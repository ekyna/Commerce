<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Stock\Entity\ResupplyAlert;
use Ekyna\Component\Commerce\Stock\Repository\ResupplyAlertRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ResupplyAlertRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertRepository extends ResourceRepository implements ResupplyAlertRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByEmailAndSubject(string $email, SubjectInterface $subject): ?ResupplyAlert
    {
        $qb = $this->createQueryBuilder('r');
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->eq('r.email', ':email'))
            ->andWhere($ex->eq('r.subjectIdentity.provider', ':provider'))
            ->andWhere($ex->eq('r.subjectIdentity.identifier', ':identifier'))
            ->getQuery()
            ->setMaxResults(1)
            ->setParameters([
                'email'      => $email,
                'provider'   => $subject::getProviderName(),
                'identifier' => $subject->getId(),
            ])
            ->getOneOrNullResult();
    }
}
