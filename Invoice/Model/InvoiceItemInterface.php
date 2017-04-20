<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Document\Model\DocumentItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface InvoiceItemInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceItemInterface extends DocumentItemInterface, SortableInterface, ResourceInterface
{
    public function getInvoice(): ?InvoiceInterface;

    public function setInvoice(?InvoiceInterface $invoice): InvoiceItemInterface;
}
