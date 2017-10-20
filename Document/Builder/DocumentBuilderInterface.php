<?php

namespace Ekyna\Component\Commerce\Document\Builder;

use Ekyna\Component\Commerce\Document\Model\DocumentInterface;

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
}
