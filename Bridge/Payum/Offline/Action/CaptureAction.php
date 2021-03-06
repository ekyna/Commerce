<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;

/**
 * Class CaptureAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CaptureAction implements ActionInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_replace([
            'factor' => false,
        ], $config);
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model[Constants::FIELD_STATUS] = $this->config['factor']
            ? Constants::STATUS_AUTHORIZED
            : Constants::STATUS_PENDING;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
