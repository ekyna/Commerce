<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

/**
 * Class EcNvpRefundAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EcNvpRefundAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Refund */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!$details['PAYERID']) {
            return;
        }

        foreach (range(0, 9) as $index) {
            if (null === $details['PAYMENTINFO_' . $index . '_PAYMENTSTATUS']) {
                continue;
            }

            $details['PAYMENTINFO_' . $index . '_PAYMENTSTATUS'] = Api::PAYMENTSTATUS_REFUNDED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == ($request instanceof Refund && $request->getModel() instanceof \ArrayAccess)) {
            return false;
        }

        // it must take into account only common payments, recurring payments must be cancelled by another action.
        $model = $request->getModel();

        return empty($model['BILLINGPERIOD']);
    }
}
