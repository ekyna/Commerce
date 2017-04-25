<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteAttachmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteAttachment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAttachment extends AbstractAttachment implements QuoteAttachmentInterface
{
    /**
     * @var QuoteInterface
     */
    protected $quote;


    /**
     * @inheritdoc
     */
    public function getSale()
    {
        return $this->getQuote();
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        if (null !== $sale && !$sale instanceof QuoteInterface) {
            throw new InvalidArgumentException('Expected instance of QuoteInterface');
        }

        $this->setQuote($sale);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @inheritdoc
     */
    public function setQuote(QuoteInterface $quote = null)
    {
        if ($quote !== $this->quote) {
            $previous = $this->quote;
            $this->quote = $quote;

            if ($previous) {
                $previous->removeAttachment($this);
            }

            if ($this->quote) {
                $this->quote->addAttachment($this);
            }
        }

        return $this;
    }
}
