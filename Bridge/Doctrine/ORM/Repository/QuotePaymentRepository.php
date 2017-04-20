<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuotePaymentRepositoryInterface;

/**
 * Class QuotePaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuotePaymentInterface findOneByKey(string $key)
 */
class QuotePaymentRepository extends AbstractPaymentRepository implements QuotePaymentRepositoryInterface
{
    public function findOneByQuoteAndKey(QuoteInterface $quote, string $key): ?QuotePaymentInterface
    {
        return $this->findOneBy([
            'quote' => $quote,
            'key'   => $key,
        ]);
    }
}
