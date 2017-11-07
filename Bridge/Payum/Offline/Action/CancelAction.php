<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;

/**
 * Class CancelAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
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

        $model[Constants::FIELD_STATUS] = Constants::STATUS_CANCELED;
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
