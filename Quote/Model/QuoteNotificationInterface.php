<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;

/**
 * Interface QuoteNotificationInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteNotificationInterface extends SaleNotificationInterface
{
    public function getQuote(): ?QuoteInterface;

    public function setQuote(?QuoteInterface $quote): QuoteNotificationInterface;
}
