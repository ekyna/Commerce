<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Refund;

/**
 * Class RefundAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RefundAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Refund $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model[Constants::FIELD_STATUS])) {
            return;
        }

        $refundableStates = [Constants::STATUS_CAPTURED, Constants::STATUS_AUTHORIZED];

        if (in_array($model[Constants::FIELD_STATUS], $refundableStates, true)) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_REFUNDED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Refund
            && $request->getModel() instanceof \ArrayAccess;
    }
}
