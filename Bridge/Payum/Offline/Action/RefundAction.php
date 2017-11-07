<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;

/**
 * Class RefundAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline\Action
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

        $model[Constants::FIELD_STATUS] = Constants::STATUS_REFUND;
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
