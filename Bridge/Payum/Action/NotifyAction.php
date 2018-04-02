<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Action;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;

/**
 * Class NotifyAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $this->gateway->execute(new Sync($payment));

        $status = new GetHumanStatus($payment);
        $this->gateway->execute($status);

        $payment->setState($status->getValue());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Notify
            //&& $request->getToken()
            && $request->getModel() instanceof PaymentInterface;
    }
}
