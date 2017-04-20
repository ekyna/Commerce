<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;

/**
 * Class PaymentDoneEventSubscriber
 * @package Ekyna\Component\Commerce\Payment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentDoneEventSubscriber
{
    private SaleTransformerInterface $saleTransformer;
    private ResourceFactoryInterface $orderFactory;
    private Payum                    $payum;


    public function __construct(
        SaleTransformerInterface $saleTransformer,
        ResourceFactoryInterface $orderFactory,
        Payum                    $payum
    ) {
        $this->saleTransformer = $saleTransformer;
        $this->orderFactory = $orderFactory;
        $this->payum = $payum;
    }

    /**
     * Payment status event handler.
     *
     * Transforms an accepted cart/quote to an order.
     *
     * @param PaymentEvent $event
     */
    public function onStatus(PaymentEvent $event)
    {
        $payment = $event->getPayment();

        $sale = $payment->getSale();

        if ($sale instanceof OrderInterface) {
            return;
        }

        if ($sale instanceof CartInterface && $sale->getState() !== CartStates::STATE_ACCEPTED) {
            return;
        }

        if ($sale instanceof QuoteInterface && $sale->getState() !== QuoteStates::STATE_ACCEPTED) {
            return;
        }

        // Store payment tokens
        $tokens = $this->findPaymentTokens($payment);

        // Transform sale to order
        if (null === $order = $this->transform($sale)) {
            return;
        }

        // Find order's transformed payment
        $newPayment = null;
        foreach ($order->getPayments() as $p) {
            if ($p->getNumber() === $payment->getNumber()) {
                $newPayment = $p;
                break;
            }
        }
        if (null === $newPayment) {
            throw new RuntimeException('Failed to find the transformed payment.');
        }

        // Convert tokens
        $this->convertTokens($this->getPaymentIdentity($newPayment), $tokens);

        // Set event new payment
        $event->setPayment($newPayment);
    }

    /**
     * Find the given payment's security tokens.
     *
     * @return array<TokenInterface>
     */
    private function findPaymentTokens(PaymentInterface $payment): array
    {
        $identity = $this->getPaymentIdentity($payment);

        return $this->payum->getTokenStorage()->findBy([
            'details' => $identity,
        ]);
    }

    /**
     * Transforms the given cart or quote into an order.
     */
    private function transform(SaleInterface $sale): ?OrderInterface
    {
        $order = $this->newOrder();

        // Initialize transformation
        $this->saleTransformer->initialize($sale, $order);

        // Transform
        if (null === $this->saleTransformer->transform()) {
            // Success
            return $order;
        }

        return null;
    }

    /**
     * @param IdentityInterface     $identity New payment's identity
     * @param array<TokenInterface> $tokens   The original payment's tokens
     */
    private function convertTokens(IdentityInterface $identity, array $tokens): void
    {
        if (empty($tokens)) {
            return;
        }

        // Update tokens identity
        $storage = $this->payum->getTokenStorage();
        foreach ($tokens as $t) {
            $t->setDetails($identity);
            $storage->update($t);
        }
    }

    /**
     * Returns the payment identity.
     */
    private function getPaymentIdentity(PaymentInterface $payment): IdentityInterface
    {
        return $this->payum->getStorage($payment)->identify($payment);
    }

    /**
     * Returns a new order.
     */
    private function newOrder(): OrderInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->orderFactory->create();
    }
}
