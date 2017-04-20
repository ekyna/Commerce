<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Quote\Model\QuoteAttachmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteAttachment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAttachment extends AbstractAttachment implements QuoteAttachmentInterface
{
    protected ?QuoteInterface $quote = null;


    /**
     * @return QuoteInterface|null
     */
    public function getSale(): ?SaleInterface
    {
        return $this->getQuote();
    }

    public function setSale(?SaleInterface $sale): SaleAttachmentInterface
    {
        if ($sale && !$sale instanceof QuoteInterface) {
            throw new UnexpectedTypeException($sale, QuoteInterface::class);
        }

        $this->setQuote($sale);

        return $this;
    }

    public function getQuote(): ?QuoteInterface
    {
        return $this->quote;
    }

    public function setQuote(?QuoteInterface $quote): QuoteAttachmentInterface
    {
        if ($quote === $this->quote) {
            return $this;
        }

        if ($previous = $this->quote) {
            $this->quote = null;
            $previous->removeAttachment($this);
        }

        if ($this->quote = $quote) {
            $this->quote->addAttachment($this);
        }

        return $this;
    }
}
