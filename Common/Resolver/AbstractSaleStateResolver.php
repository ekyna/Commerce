<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

use function array_diff_assoc;

/**
 * Class AbstractSaleStateResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleStateResolver extends AbstractStateResolver
{
    protected StateResolverInterface $paymentStateResolver;
    protected StateResolverInterface $shipmentStateResolver;
    protected StateResolverInterface $invoiceStateResolver;

    public function setPaymentStateResolver(StateResolverInterface $resolver): void
    {
        $this->paymentStateResolver = $resolver;
    }

    public function setShipmentStateResolver(StateResolverInterface $resolver): void
    {
        $this->shipmentStateResolver = $resolver;
    }

    public function setInvoiceStateResolver(StateResolverInterface $resolver): void
    {
        $this->invoiceStateResolver = $resolver;
    }

    /**
     * @inheritDoc
     *
     * @param SaleInterface $subject
     */
    public function resolve(object $subject): bool
    {
        $this->supports($subject);

        $old = ['state' => $subject->getState()];
        if ($subject instanceof ShipmentSubjectInterface) {
            $old['shipment'] = $subject->getShipmentState();
            $this->shipmentStateResolver->resolve($subject);
        }
        if ($subject instanceof InvoiceSubjectInterface) {
            $old['invoice'] = $subject->getInvoiceState();
            if ($subject->isSample()) {
                if ($subject->getInvoiceState() !== InvoiceStates::STATE_COMPLETED) {
                    $subject->setInvoiceState(InvoiceStates::STATE_COMPLETED);
                }
            } else {
                $this->invoiceStateResolver->resolve($subject);
            }
        }
        if ($subject instanceof PaymentSubjectInterface) {
            $old['payment'] = $subject->getPaymentState();
            if ($subject->isSample()) {
                if ($subject->getPaymentState() !== PaymentStates::STATE_COMPLETED) {
                    $subject->setPaymentState(PaymentStates::STATE_COMPLETED);
                }
            } else {
                $this->paymentStateResolver->resolve($subject);
            }
        }

        $state = $this->resolveState($subject);

        $new = ['state' => $state];
        if ($subject instanceof PaymentSubjectInterface) {
            $new['payment'] = $subject->getPaymentState();
        }
        if ($subject instanceof ShipmentSubjectInterface) {
            $new['shipment'] = $subject->getShipmentState();
        }
        if ($subject instanceof InvoiceSubjectInterface) {
            $new['invoice'] = $subject->getInvoiceState();
        }

        if (empty(array_diff_assoc($old, $new))) {
            return false;
        }

        $subject->setState($state);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof SaleInterface) {
            throw new UnexpectedTypeException($subject, SaleInterface::class);
        }
    }
}
