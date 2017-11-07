<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Request\Hang;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

/**
 * Class HangAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HangAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Hang $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Hang
            && $request->getModel() instanceof \ArrayAccess;
    }
}
