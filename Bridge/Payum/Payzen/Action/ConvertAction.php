<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Payzen\Action;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

/**
 * Class ConvertAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Payzen\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false == $model['vads_amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrency()->getCode()));
            if (2 < $currency->exp) {
                throw new RuntimeException('Unexpected currency exp.');
            }

            $model['vads_currency'] = (string)$currency->numeric;
            // Amount in cents
            $model['vads_amount'] = (string)abs($payment->getAmount() * pow(10, $currency->exp));
        }

        $sale = $payment->getSale();

        if (false == $model['vads_order_id']) {
            $model['vads_order_id'] = $sale->getNumber();
        }
        if (false == $model['vads_order_info']) {
            $model['vads_order_info'] = 'Payment ID: ' . $payment->getNumber();
        }
        if (false == $model['vads_cust_id'] && null !== $customer = $sale->getCustomer()) {
            $model['vads_cust_id'] = $customer->getNumber();
        }
        if (false == $model['vads_cust_email']) {
            $model['vads_cust_email'] = $sale->getEmail();
        }

        $request->setResult((array)$model);
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
