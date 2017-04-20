<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;

/**
 * Interface QuoteAttachmentInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteAttachmentInterface extends SaleAttachmentInterface
{
    public function getQuote(): ?QuoteInterface;

    public function setQuote(?QuoteInterface $quote): QuoteAttachmentInterface;
}
