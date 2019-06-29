<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;

/**
 * Class EcNvpCancelAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EcNvpCancelAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Cancel */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['PAYERID']) {
            return;
        }

        $details['CANCELLED'] = true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == ($request instanceof Cancel && $request->getModel() instanceof \ArrayAccess)) {
            return false;
        }

        // it must take into account only common payments, recurring payments must be cancelled by another action.
        $model = $request->getModel();

        return empty($model['BILLINGPERIOD']);
    }
}
