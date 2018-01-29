<?php

namespace Ekyna\Component\Commerce\Document\Builder;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineInterface;

/**
 * Interface DocumentBuilderInterface
 * @package Ekyna\Component\Commerce\Document\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentBuilderInterface
{
    /**
     * Builds the invoice.
     *
     * @param DocumentInterface $document
     */
    public function build(DocumentInterface $document);

    /**
     * Updates the document's data (currency, customer and addresses).
     *
     * @param DocumentInterface $document
     *
     * @return bool
     */
    public function update(DocumentInterface $document);

    /**
     * Builds the document good line from the given sale item.
     *
     * @param Common\SaleItemInterface $item
     * @param DocumentInterface        $document
     *
     * @return DocumentLineInterface|null
     */
    public function buildGoodLine(Common\SaleItemInterface $item, DocumentInterface $document);

    /**
     * Builds the discount line from the given adjustment.
     *
     * @param Common\SaleAdjustmentInterface $adjustment
     * @param DocumentInterface          $document
     *
     * @return DocumentLineInterface|null
     */
    public function buildDiscountLine(Common\SaleAdjustmentInterface $adjustment, DocumentInterface $document);

    /**
     * Builds the document's shipment line.
     *
     * @param DocumentInterface $document
     *
     * @return DocumentLineInterface|null
     */
    public function buildShipmentLine(DocumentInterface $document);
}
