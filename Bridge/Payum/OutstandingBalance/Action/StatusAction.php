<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

/**
 * Class StatusAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var GetStatusInterface $request  */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model[Constants::FIELD_STATUS])) {
            $request->markNew();

            return;
        }

        if (Constants::STATUS_AUTHORIZED == $model[Constants::FIELD_STATUS]) {
            $request->markAuthorized();

            return;
        }

        if (Constants::STATUS_CAPTURED == $model[Constants::FIELD_STATUS]) {
            $request->markCaptured();

            return;
        }

        if (Constants::STATUS_FAILED == $model[Constants::FIELD_STATUS]) {
            $request->markFailed();

            return;
        }

        if (Constants::STATUS_EXPIRED == $model[Constants::FIELD_STATUS]) {
            $request->markExpired();

            return;
        }

        if (Constants::STATUS_CANCELED == $model[Constants::FIELD_STATUS]) {
            $request->markCanceled();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
