<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use DateTime;
use Ekyna\Component\Commerce\Common\Locking\LockResolverInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class OrderPaymentLockResolver
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentLockResolver implements LockResolverInterface
{
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

        if ($method->isFactor() && (PaymentStates::STATE_AUTHORIZED === $resource->getState())) {
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
