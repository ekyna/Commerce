<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;

/**
 * Class ConvertAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if (isset($details[Constants::FIELD_STATUS])) {
            return;
        }

        if (null === $sale = $payment->getSale()) {
            throw new RuntimeException("Payment's sale must be defined at this point.");
        }
        if (null === $customer = $sale->getCustomer()) {
            throw new RuntimeException("Sale's customer must be defined at this point.");
        }

        // Use parent if available
        if ($customer->hasParent()) {
            $customer = $customer->getParent();
        }

        // If sale has a customer limit
        if (0 < $limit = $sale->getOutstandingLimit()) {
            // Use sale's balance
            $balance = - $sale->getOutstandingAccepted() - $sale->getOutstandingExpired();
        } else {
            // Use customer's limit and balance
            $limit = $customer->getOutstandingLimit();
            $balance = $customer->getOutstandingBalance();
        }

        $details->defaults(array(
            Constants::FIELD_STATUS  => null,
            Constants::FIELD_AMOUNT  => $payment->getRealAmount(), // Using default currency
            Constants::FIELD_LIMIT   => $limit,                    // Using default currency
            Constants::FIELD_BALANCE => $balance,                  // Using default currency
        ));

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (!($request instanceof Convert && $request->getTo() === 'array')) {
            return false;
        }

        $payment = $request->getSource();

        return $payment instanceof PaymentInterface && !$payment->isRefund();
    }
}
