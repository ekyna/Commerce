<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface QuoteItemInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuoteInterface getSale()
 */
interface QuoteItemInterface extends SaleItemInterface
{
    public function getQuote(): ?QuoteInterface;

    public function setQuote(?QuoteInterface $quote): QuoteItemInterface;
}
