<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;

/**
 * Class QuoteRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuoteInterface|null findOneById(int $id)
 * @method QuoteInterface|null findOneByKey(string $key)
 * @method QuoteInterface|null findOneByNumber(string $number)
 */
class QuoteRepository extends AbstractSaleRepository implements QuoteRepositoryInterface
{
    public function findByInitiatorCustomer(CustomerInterface $initiator): array
    {
        $qb = $this->createQueryBuilder('q');
        $ex = $qb->expr();

        $qb->where($ex->eq('q.initiatorCustomer', ':initiator'));

        if ($initiator->hasChildren()) {
            $qb
                ->join('q.initiatorCustomer', 'i')
                ->orWhere($ex->eq('i.parent', ':initiator'));
        }

        return $qb
            ->getQuery()
            ->setParameters([
                'initiator' => $initiator,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    protected function getAlias(): string
    {
        return 'q';
    }
}
