<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractInvoice
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoice extends Document implements Invoice\InvoiceInterface
{
    use NumberSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = Invoice\InvoiceTypes::TYPE_INVOICE;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getNumber();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
}
