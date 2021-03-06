<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class AbstractStateResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStateResolver implements StateResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(object $subject): bool
    {
        $this->supports($subject);

        $state = $this->resolveState($subject);

        if ($state !== $subject->getState()) {
            $subject->setState($state);

            return true;
        }

        return false;
    }

    /**
     * Resolves the subject's state.
     *
     * @param object $subject
     *
     * @return string
     */
    abstract protected function resolveState(object $subject): string;

    /**
     * Throws an exception if subject is not supported.
     *
     * @param object $subject
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof StateSubjectInterface) {
            throw new UnexpectedTypeException($subject, StateSubjectInterface::class);
        }
    }
}
