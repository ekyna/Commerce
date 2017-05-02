<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;

/**
 * Class SyncAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SyncAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Sync
            && $request->getModel() instanceof \ArrayAccess;
    }
}
