<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use DateTime;
use Ekyna\Component\Commerce\Common\Locking\LockResolverInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class OrderPaymentLockResolver
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentLockResolver implements LockResolverInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritDoc
     */
    public function support(ResourceInterface $resource): bool
    {
        if (!$resource instanceof OrderPaymentInterface) {
            return false;
        }

        $method = $resource->getMethod();

        if ($method->isOutstanding()) {
            return false;
        }

        if (!$method->isFactor()) {
            return true;
        }

        $stateCs = $this->persistenceHelper->getChangeSet($resource, 'state');

        $state = !empty($stateCs) ? $stateCs[0] : $resource->getState();

        if (PaymentStates::STATE_AUTHORIZED === $state) {
            return false;
        }

        if (!PaymentStates::isCompletedState($state)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param OrderPaymentInterface $resource
     */
    public function resolve(ResourceInterface $resource): ?DateTime
    {
        return $resource->getCompletedAt();
    }
}
