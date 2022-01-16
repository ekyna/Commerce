<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;

/**
 * Class ConvertAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline\Action
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

        $currency = $payment->getCurrency()->getCode();

        $details[Constants::FIELD_STATUS] = false;
        $details['amount'] = Money::fixed($payment->getAmount(), $currency);
        $details['currency'] = $currency;

        $request->setResult((array)$details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }
}
