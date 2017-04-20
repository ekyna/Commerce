<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Payment\Entity\PaymentTerm;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentTermRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class PaymentTermRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermRepository extends TranslatableRepository implements PaymentTermRepositoryInterface
{
    /**
     * @var PaymentTerm
     */
    private $longestTerm = false;


    /**
     * @inheritDoc
     */
    public function findLongest(): ?PaymentTermInterface
    {
        if (false !== $this->longestTerm) {
            return $this->longestTerm;
        }

        $qb = $this->createQueryBuilder('t');
        $query = $qb
            ->addOrderBy('t.days', 'DESC')
            ->addOrderBy('t.endOfMonth', 'DESC')
            ->getQuery()
            ->setMaxResults(1);

        return $this->longestTerm = $query->getOneOrNullResult();
    }
}
