<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action;

use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants;
use Ekyna\Component\Commerce\Bridge\Payum\Request\Accept;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

/**
 * Class AcceptAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AcceptAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Accept $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model[Constants::FIELD_STATUS] = Constants::STATUS_CAPTURED;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Accept
            && $request->getModel() instanceof \ArrayAccess;
    }
}
