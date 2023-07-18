<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Ekyna\Component\Commerce\Order\Manager\OrderInvoiceManagerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;

/**
 * Class OrderInvoiceManager
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceManager extends ResourceManager implements OrderInvoiceManagerInterface
{
    use UpdateMarginTrait;

    /**
     * @inheritDoc
     */
    public function updateMargin(OrderInvoiceInterface $invoice): void
    {
        $this->updateMarginSubject($invoice);
    }
}
