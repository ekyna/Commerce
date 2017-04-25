<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMessage;

/**
 * Class PaymentMessage
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMessage extends AbstractMessage
{
    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return PaymentMessageTranslation::class;
    }
}
