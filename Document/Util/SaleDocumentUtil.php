<?php

namespace Ekyna\Component\Commerce\Document\Util;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;

/**
 * Class SaleDocumentUtil
 * @package Ekyna\Component\Commerce\Document\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleDocumentUtil
{
    /**
     * Returns the types of the sale editable documents.
     *
     * @param SaleInterface $sale
     *
     * @return array
     */
    static public function getSaleEditableDocumentTypes(SaleInterface $sale)
    {
        $types = [];

        foreach (DocumentTypes::getTypes() as $type) {
            if (!static::isSaleSupportsDocumentType($sale, $type)) {
                continue;
            }

            foreach ($sale->getAttachments() as $attachment) {
                if ($attachment->getType() === $type) {
                    continue 2;
                }
            }

            $types[] = $type;
        }

        return $types;
    }

    /**
     * Returns whether the sale supports the given document type.
     *
     * @param SaleInterface $sale
     * @param string        $type
     *
     * @return bool
     */
    static public function isSaleSupportsDocumentType(SaleInterface $sale, $type)
    {
        if (!DocumentTypes::isValidType($type)) {
            return false;
        }

        if (null === $class = DocumentTypes::getClass($type)) {
            return false;
        }

        if (is_subclass_of($sale, $class)) {
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
