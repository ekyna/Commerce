<?php

declare(strict_types=1);

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
     */
    public function build(DocumentInterface $document): void;

    /**
     * Updates the document's data (currency, customer and addresses).
     */
    public function update(DocumentInterface $document): bool;

    /**
     * Builds the document good line from the given sale item.
     */
    public function buildGoodLine(
        Common\SaleItemInterface $item,
        DocumentInterface $document
    ): ?DocumentLineInterface;

    /**
     * Builds the discount line from the given adjustment.
     */
    public function buildDiscountLine(
        Common\SaleAdjustmentInterface $adjustment,
        DocumentInterface              $document
    ): ?DocumentLineInterface;

    /**
     * Builds the document's shipment line.
     */
    public function buildShipmentLine(DocumentInterface $document): ?DocumentLineInterface;
}
