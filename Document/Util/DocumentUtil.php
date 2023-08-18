<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Util;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model as Document;

/**
 * Class DocumentUtil
 * @package Ekyna\Component\Commerce\Document\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DocumentUtil
{
    public static function findWithType(Common\SaleInterface $sale, string $type): ?Common\SaleAttachmentInterface
    {
        foreach ($sale->getAttachments() as $attachment) {
            if ($attachment->getType() === $type) {
                return $attachment;
            }
        }

        return null;
    }

    /**
     * Returns the types of the sale editable documents.
     *
     * @return array<string>
     */
    public static function getSaleEditableDocumentTypes(Common\SaleInterface $sale, bool $noDuplicate = true): array
    {
        $types = [];

        foreach (Document\DocumentTypes::getSaleTypes() as $type) {
            if (!DocumentUtil::isSaleSupportsDocumentType($sale, $type)) {
                continue;
            }

            if ($noDuplicate && DocumentUtil::findWithType($sale, $type)) {
                continue;
            }

            $types[] = $type;
        }

        return $types;
    }

    /**
     * Returns whether the sale supports the given document type.
     *
     * @param Common\SaleInterface $sale
     * @param string               $type
     *
     * @return bool
     */
    public static function isSaleSupportsDocumentType(Common\SaleInterface $sale, string $type): bool
    {
        if (!Document\DocumentTypes::isValidSaleType($type)) {
            return false;
        }

        if (empty($classes = Document\DocumentTypes::getClasses($type))) {
            return false;
        }

        foreach ($classes as $class) {
            if (is_subclass_of($sale, $class)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Finds the document good line for the given sale item.
     *
     * @param Document\DocumentInterface $document
     * @param Common\SaleItemInterface   $item
     *
     * @return Document\DocumentLineInterface|null
     */
    public static function findGoodLine(
        Document\DocumentInterface $document,
        Common\SaleItemInterface   $item
    ): ?Document\DocumentLineInterface {
        foreach ($document->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD) as $line) {
            if ($line->getSaleItem() === $item) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Returns whether the document contains one of the given sale item's parents.
     *
     * @param Document\DocumentInterface $document
     * @param Common\SaleItemInterface   $item
     *
     * @return bool
     */
    public static function hasPublicParent(
        Document\DocumentInterface $document,
        Common\SaleItemInterface   $item
    ): bool {
        if (!$item->isPrivate()) {
            return true;
        }

        if (DocumentUtil::findGoodLine($document, $item->getPublicParent())) {
            return true;
        }

        return false;
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
