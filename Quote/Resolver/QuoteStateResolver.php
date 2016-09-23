<?php

namespace Ekyna\Component\Commerce\Quote\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteStateResolver
 * @package Ekyna\Component\Commerce\Quote\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StateSubjectInterface $subject)
    {
        if (!$subject instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface.");
        }

        // TODO: Implement resolve() method.

        return $subject->getState();
    }
}
