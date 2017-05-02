<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;

/**
 * Class CancelAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CancelAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Cancel $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model[Constants::FIELD_STATUS])) {
            return;
        }

        $cancellableStates = [Constants::STATUS_CAPTURED, Constants::STATUS_AUTHORIZED];

        if (in_array($model[Constants::FIELD_STATUS], $cancellableStates, true)) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_CANCELLED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Cancel
            && $request->getModel() instanceof \ArrayAccess;
    }
}
