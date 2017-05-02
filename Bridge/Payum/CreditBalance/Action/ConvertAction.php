<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;

/**
 * Class ConvertAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action
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

        $details->defaults(array(
            Constants::FIELD_STATUS  => null,
            Constants::FIELD_AMOUNT  => $payment->getAmount(),
            Constants::FIELD_BALANCE => $customer->getCreditBalance(),
        ));

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
