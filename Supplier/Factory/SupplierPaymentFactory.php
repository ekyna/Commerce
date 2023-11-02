<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Factory;

use DateTime;
use Ekyna\Component\Commerce\Supplier\Model\SupplierPaymentInterface;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierPaymentFactory
 * @package Ekyna\Component\Commerce\Supplier\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierPaymentFactory extends ResourceFactory implements SupplierPaymentFactoryInterface
{
    public function create(): ResourceInterface
    {
        /** @var SupplierPaymentInterface $payment */
        $payment = parent::create();

        $payment->setExchangeDate(new DateTime());

        return $payment;
    }

    public function createFromContext(Context $context): ResourceInterface
    {
        /** @var SupplierPaymentInterface $payment */
        $payment = parent::createFromContext($context);

        if (null !== $order = $payment->getOrder()) {
            $payment->setCurrency($order->getCurrency());
        }

        return $payment;
    }
}
