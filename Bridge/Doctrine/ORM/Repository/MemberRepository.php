<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class MemberRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberRepository extends ResourceRepository implements MemberRepositoryInterface
{
    /**
     * @var Query
     */
    private $findOneByGatewayAndIdentifierQuery;

    /**
     * @var Query
     */
    private $findOneByGatewayAndEmailQuery;

    /**
     * @var Query
     */
    private $findOneByAudienceAndEmailQuery;


    /**
     * @inheritDoc
     */
    public function findOneByGatewayAndIdentifier(string $gateway, string $identifier): ?MemberInterface
    {
        return $this
            ->getFindOneByGatewayAndIdentifier()
            ->setParameters([
                'gateway'    => $gateway,
                'identifier' => $identifier,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByGatewayAndEmail(string $gateway, string $email): ?MemberInterface
    {
        return $this
            ->getFindOneByGatewayAndEmailQuery()
            ->setParameters([
                'gateway' => $gateway,
                'email'   => $email,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByAudienceAndEmail(AudienceInterface $audience, string $email): ?MemberInterface
    {
        return $this
            ->getFindOneByAudienceAndEmailQuery()
            ->setParameters([
                'audience' => $audience,
                'email'    => $email,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByGatewayAndExcludingIds(string $gateway, array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $qb = $this->createQueryBuilder('m');

        return $qb
            ->join('m.audience', 'a')
            ->andWhere($qb->expr()->eq('a.gateway', ':gateway'))
            ->andWhere($qb->expr()->notIn('m.identifier', ':identifiers'))
            ->getQuery()
            ->setParameters([
                'gateway'     => $gateway,
                'identifiers' => $identifiers,
            ])
            ->getResult();
    }

    /**
     * @return Query
     */
    private function getFindOneByGatewayAndIdentifier(): Query
    {
        if ($this->findOneByGatewayAndIdentifierQuery) {
            return $this->findOneByGatewayAndIdentifierQuery;
        }

        $qb = $this->createQueryBuilder('m');

        return $this->findOneByGatewayAndIdentifierQuery = $qb
            ->join('m.audience', 'a')
            ->andWhere($qb->expr()->eq('a.gateway', ':gateway'))
            ->andWhere($qb->expr()->eq('m.identifier', ':identifier'))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return Query
     */
    private function getFindOneByGatewayAndEmailQuery(): Query
    {
        if ($this->findOneByGatewayAndEmailQuery) {
            return $this->findOneByGatewayAndEmailQuery;
        }

        $qb = $this->createQueryBuilder('m');

        return $this->findOneByGatewayAndEmailQuery = $qb
            ->join('m.audience', 'a')
            ->andWhere($qb->expr()->eq('a.gateway', ':gateway'))
            ->andWhere($qb->expr()->eq('m.email', ':email'))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return Query
     */
    private function getFindOneByAudienceAndEmailQuery(): Query
    {
        if ($this->findOneByAudienceAndEmailQuery) {
            return $this->findOneByAudienceAndEmailQuery;
        }

        $qb = $this->createQueryBuilder('m');

        return $this->findOneByAudienceAndEmailQuery = $qb
            ->join('m.audience', 'a')
            ->andWhere($qb->expr()->eq('m.audience', ':audience'))
            ->andWhere($qb->expr()->eq('m.email', ':email'))
            ->getQuery()
            ->useQueryCache(true);
    }
}
