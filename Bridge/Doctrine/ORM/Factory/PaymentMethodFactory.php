<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory;

use Ekyna\Component\Commerce\Payment\Entity\PaymentMessage;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\TranslatableFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class PaymentMethodFactory
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodFactory extends TranslatableFactory
{
    /**
     * @inheritDoc
     */
    public function create(): ResourceInterface
    {
        /** @var PaymentMethodInterface $method */
        $method = parent::create();

        foreach (PaymentStates::getNotifiableStates() as $state) {
            $message = new PaymentMessage();
            $method->addMessage($message->setState($state));
        }

        return $method;
    }
}
