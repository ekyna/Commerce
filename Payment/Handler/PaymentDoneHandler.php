<?php

namespace Ekyna\Component\Commerce\Payment\Handler;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;

/**
 * Class PaymentDoneHandler
 * @package Ekyna\Component\Commerce\Payment\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentDoneHandler
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
     * Returns payum.
     *
     * @return Payum
     */
    public function getPayum()
    {
        return $this->payum;
    }

    /**
     * Handle the payment when it's done (status/notify).
     *
     * @param PaymentInterface $payment
     *
     * @return \Ekyna\Component\Commerce\Common\Model\SaleInterface The resulting sale
     */
    public function handle(PaymentInterface $payment)
    {
        $sale = $payment->getSale();

        if ($sale instanceof OrderInterface) {
            return $sale;
        } elseif ($sale instanceof CartInterface) {
            if ($sale->getState() !== CartStates::STATE_ACCEPTED) {
                return $sale;
            }
        } elseif ($sale instanceof QuoteInterface) {
            if ($sale->getState() !== QuoteStates::STATE_ACCEPTED) {
                return $sale;
            }
        }

        $tokens = $this->findPaymentTokens($payment);

        if (null !== $order = $this->transform($sale)) {
            $this->convertTokens($order, $payment, $tokens);

            return $order;
        }

        return $sale;
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
            'details' => serialize($identity),
        ]);

        return $tokens;
    }

    /**
     * Transforms the given cart to an order.
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
     * @param OrderInterface   $order   The transformation result sale
     * @param PaymentInterface $payment The original payment
     * @param TokenInterface[] $tokens  The original payment's tokens
     */
    private function convertTokens(OrderInterface $order, PaymentInterface $payment, array $tokens)
    {
        if (empty($tokens)) {
            return;
        }

        // Find order's transformed payment
        $identity = null;
        foreach ($order->getPayments() as $p) {
            if ($p->getNumber() === $payment->getNumber()) {
                $identity = $this->getPaymentIdentity($p);
                break;
            }
        }

        if (null === $identity) {
            throw new RuntimeException("Failed to convert payment tokens after sale transformation.");
        }

        // Update tokens identity
        $storage = $this->payum->getTokenStorage();
        foreach ($tokens as $t) {
            /** @noinspection PhpParamsInspection */
            $t->setDetails(serialize($identity));
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
