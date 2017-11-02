<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;

/**
 * Class AuthorizeAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AuthorizeAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model[Constants::FIELD_STATUS])) {
            return;
        }
        if (!isset($model[Constants::FIELD_AMOUNT], $model[Constants::FIELD_LIMIT], $model[Constants::FIELD_BALANCE])) {
            throw new RuntimeException("Payment has not been converted.");
        }

        if ($model[Constants::FIELD_LIMIT] <= $model[Constants::FIELD_BALANCE] - $model[Constants::FIELD_AMOUNT]) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_CAPTURED;
        } else {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_FAILED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
