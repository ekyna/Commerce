<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Common\Listener\UploadableListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SaleTransformer
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Use a "resource service factory" to get operators
 */
class SaleTransformer implements SaleTransformerInterface
{
    /**
     * @var SaleCopierFactoryInterface
     */
    private $saleCopierFactory;

    /**
     * @var ResourceOperatorInterface
     */
    private $cartOperator;

    /**
     * @var ResourceOperatorInterface
     */
    private $quoteOperator;

    /**
     * @var ResourceOperatorInterface
     */
    private $orderOperator;

    /**
     * @var UploadableListener
     */
    private $uploadableListener;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SaleInterface
     */
    protected $source;

    /**
     * @var SaleInterface
     */
    protected $target;


    /**
     * Constructor.
     *
     * @param SaleCopierFactoryInterface $saleCopierFactory
     * @param ResourceOperatorInterface  $cartOperator
     * @param ResourceOperatorInterface  $quoteOperator
     * @param ResourceOperatorInterface  $orderOperator
     * @param UploadableListener         $uploadableListener
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        SaleCopierFactoryInterface $saleCopierFactory,
        ResourceOperatorInterface $cartOperator,
        ResourceOperatorInterface $quoteOperator,
        ResourceOperatorInterface $orderOperator,
        UploadableListener $uploadableListener,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->saleCopierFactory  = $saleCopierFactory;
        $this->cartOperator       = $cartOperator;
        $this->quoteOperator      = $quoteOperator;
        $this->orderOperator      = $orderOperator;
        $this->uploadableListener = $uploadableListener;
        $this->eventDispatcher    = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function initialize(SaleInterface $source, SaleInterface $target)
    {
        $this->source = $source;
        $this->target = $target;

        $event = $this->getOperator($this->target)->initialize($this->target);
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch(SaleTransformEvents::PRE_COPY, $event);
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $this
            ->saleCopierFactory
            ->create($this->source, $this->target)
            ->copySale();

        $this->eventDispatcher->dispatch(SaleTransformEvents::POST_COPY, $event);

        return $event;
    }

    /**
     * @inheritDoc
     */
    public function transform()
    {
        if (null === $this->source || null === $this->target) {
            throw new LogicException("Please call initialize first.");
        }

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch(SaleTransformEvents::PRE_TRANSFORM, $event);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Persist the target sale
        $targetEvent = $this->getOperator($this->target)->persist($this->target);
        if (!$targetEvent->isPropagationStopped() && !$targetEvent->hasErrors()) {
            $this->getOperator($this->target)->refresh($this->target);

            // Disable the uploadable listener
            $this->uploadableListener->setEnabled(false);

            // Delete the source sale
            $sourceEvent = $this->getOperator($this->source)->delete($this->source, true); // Hard delete
            if (!$sourceEvent->isPropagationStopped() && !$sourceEvent->hasErrors()) {
                $targetEvent = null;
            }

            // Enable the uploadable listener
            $this->uploadableListener->setEnabled(true);
        }

        $this->eventDispatcher->dispatch(SaleTransformEvents::POST_TRANSFORM, $event);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Unset source and target sales
        $this->source = null;
        $this->target = null;

        return $targetEvent;
    }

    /**
     * Returns the proper operator for the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return ResourceOperatorInterface
     */
    protected function getOperator(SaleInterface $sale)
    {
        if ($sale instanceof CartInterface) {
            return $this->cartOperator;
        } elseif ($sale instanceof QuoteInterface) {
            return $this->quoteOperator;
        } elseif ($sale instanceof OrderInterface) {
            return $this->orderOperator;
        }

        throw new InvalidArgumentException("Unexpected sale type.");
    }
}
