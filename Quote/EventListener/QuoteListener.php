<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteEventInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\PersistenceEvent;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteListener extends AbstractSaleListener
{
    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $numberGenerator
     * @param KeyGeneratorInterface    $keyGenerator
     * @param CalculatorInterface      $calculator
     * @param StateResolverInterface   $stateResolver
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        KeyGeneratorInterface $keyGenerator,
        CalculatorInterface $calculator,
        StateResolverInterface $stateResolver
    ) {
        parent::__construct($numberGenerator, $keyGenerator, $calculator);

        $this->stateResolver = $stateResolver;
    }

    /**
     * @inheritdoc
     */
    public function onInsert(PersistenceEvent $event)
    {
        $sale = $this->getSaleFromEvent($event);

        parent::onInsert($event);

        // TODO resolve states
    }

    /**
     * @inheritdoc
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $sale = $this->getSaleFromEvent($event);

        parent::onUpdate($event);

        // TODO resolve states
    }

    /**
     * Pre delete event handler.
     *
     * @param QuoteEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(QuoteEventInterface $event)
    {
        $quote = $event->getQuote();

        // Stop if quote has valid payments
        if (null !== $payments = $quote->getPayments()) {
            $deletablePaymentStates = [PaymentStates::STATE_NEW, PaymentStates::STATE_CANCELLED];
            foreach ($payments as $payment) {
                if (!in_array($payment->getState(), $deletablePaymentStates)) {
                    throw new IllegalOperationException();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(PersistenceEvent $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface");
        }

        return $resource;
    }
}
