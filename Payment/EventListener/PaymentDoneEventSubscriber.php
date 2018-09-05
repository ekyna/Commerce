<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
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
    /**
     * @var SaleTransformerInterface
     */
    private $saleTransformer;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Payum
     */
    private $payum;


    /**
     * Constructor.
     *
     * @param SaleTransformerInterface $saleTransformer
     * @param OrderRepositoryInterface $orderRepository
     * @param Payum                    $payum
     */
    public function __construct(
        SaleTransformerInterface $saleTransformer,
        OrderRepositoryInterface $orderRepository,
        Payum $payum
    ) {
        $this->saleTransformer = $saleTransformer;
        $this->orderRepository = $orderRepository;
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
            throw new RuntimeException("Failed to find the transformed payment.");
        }

        // Convert tokens
        $this->convertTokens($this->getPaymentIdentity($newPayment), $tokens);

        // Set event new payment
        $event->setPayment($newPayment);
    }

    /**
     * Find the given payment's security tokens.
     *
     * @param PaymentInterface $payment
     *
     * @return TokenInterface[]
     */
    private function findPaymentTokens(PaymentInterface $payment)
    {
        $identity = $this->getPaymentIdentity($payment);

        /** @var TokenInterface[] $tokens */
        $tokens = $this->payum->getTokenStorage()->findBy([
            'details' => $identity,
        ]);

        return $tokens;
    }

    /**
     * Transforms the given cart or quote to an order.
     *
     * @param SaleInterface $sale
     *
     * @return OrderInterface|null
     */
    private function transform(SaleInterface $sale)
    {
        $order = $this->newOrder();

        // Initialize transformation
        $this->saleTransformer->initialize($sale, $order);

        // Transform
        if (null === $event = $this->saleTransformer->transform()) {
            // Success
            return $order;
        }

        return null;
    }

    /**
     * @param IdentityInterface $identity New payment's identity
     * @param TokenInterface[]  $tokens   The original payment's tokens
     */
    private function convertTokens(IdentityInterface $identity, array $tokens)
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
     *
     * @param PaymentInterface $payment
     *
     * @return \Payum\Core\Storage\IdentityInterface
     */
    private function getPaymentIdentity(PaymentInterface $payment)
    {
        return $this->payum->getStorage($payment)->identify($payment);
    }

    /**
     * Returns a new order.
     *
     * @return \Ekyna\Bundle\CommerceBundle\Model\OrderInterface
     */
    private function newOrder()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->orderRepository->createNew();
    }
}
