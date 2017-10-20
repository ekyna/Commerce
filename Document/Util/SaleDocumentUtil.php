<?php

namespace Ekyna\Component\Commerce\Document\Util;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;

/**
 * Class SaleDocumentUtil
 * @package Ekyna\Component\Commerce\Document\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleDocumentUtil
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
            if (!is_subclass_of($sale, DocumentTypes::getClass($type))) {
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
}
