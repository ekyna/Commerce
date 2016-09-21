<?php


namespace Ekyna\Component\Commerce\Quote\Resolver;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Interface StateResolverInterface
 * @package Ekyna\Component\Commerce\Quote\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StateResolverInterface
{
    /**
     * Resolves the quote state.
     *
     * @param QuoteInterface $quote
     *
     * @return StateResolverInterface
     * @throws CommerceExceptionInterface
     */
    public function resolve(QuoteInterface $quote);
}
