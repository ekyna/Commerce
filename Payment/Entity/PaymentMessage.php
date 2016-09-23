<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMessage;

/**
 * Class PaymentMessage
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMessage extends AbstractMessage
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return PaymentMessageTranslation::class;
    }
}
