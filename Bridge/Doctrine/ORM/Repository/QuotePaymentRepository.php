<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuotePaymentRepositoryInterface;

/**
 * Class QuotePaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuotePaymentInterface|null findOneByKey($key)
 */
class QuotePaymentRepository extends AbstractPaymentRepository implements QuotePaymentRepositoryInterface
{
    /**
     * @return void
     */
    public function createNew()
    {
        throw new RuntimeException("Disabled: use payment factory.");
    }
}
