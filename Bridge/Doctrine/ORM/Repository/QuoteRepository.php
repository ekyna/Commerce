<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

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
    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'q';
    }
}
