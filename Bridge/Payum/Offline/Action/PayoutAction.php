<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants;
use Ekyna\Component\Commerce\Bridge\Payum\Request\Hang;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Payout;

/**
 * Class PayoutAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PayoutAction implements ActionInterface
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

        $model[Constants::FIELD_STATUS] = Constants::STATUS_PAYEDOUT;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Payout
            && $request->getModel() instanceof \ArrayAccess;
    }
}
