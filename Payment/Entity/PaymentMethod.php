<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class PaymentMethod
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethod extends AbstractMethod
{
    /**
     * @inheritdoc
     */
    protected function validateMessageClass(MessageInterface $message)
    {
        if (!$message instanceof PaymentMessage) {
            throw new InvalidArgumentException("Expected instance of PaymentMessage.");
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return PaymentMethodTranslation::class;
    }
}
