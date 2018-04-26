<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Listener\UploadableListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;

/**
 * Class SaleTransformer
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Use a "resource service factory" to get operators
 * @TODO    Use event dispatcher for pre/post copy and pre/post transform
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
     * @param ResourceOperatorInterface $cartOperator
     * @param ResourceOperatorInterface $quoteOperator
     * @param ResourceOperatorInterface $orderOperator
     * @param UploadableListener        $uploadableListener
     */
    public function __construct(
        SaleCopierFactoryInterface $saleCopierFactory,
        ResourceOperatorInterface $cartOperator,
        ResourceOperatorInterface $quoteOperator,
        ResourceOperatorInterface $orderOperator,
        UploadableListener $uploadableListener
    ) {
        $this->saleCopierFactory = $saleCopierFactory;
        $this->cartOperator = $cartOperator;
        $this->quoteOperator = $quoteOperator;
        $this->orderOperator = $orderOperator;
        $this->uploadableListener = $uploadableListener;
    }

    /**
     * @inheritdoc
     */
    public function initialize(SaleInterface $source, SaleInterface $target)
    {
        // TODO Use event
        if ($source instanceof OrderInterface) {
            // Prevent if order is not 'new'
            if ($source->getState() !== OrderStates::STATE_NEW) {
                $event = new ResourceEvent();
                $event->addMessage(new ResourceMessage(
                    'ekyna_commerce.sale.message.transform_prevented',
                    ResourceMessage::TYPE_ERROR
                ));

                return $event;
            }
        }

        $this->source = $source;
        $this->target = $target;

        $this
            ->saleCopierFactory
            ->create($this->source, $this->target)
            ->copySale();

        $this->postCopy(); // TODO Use event

        return $this->getOperator($this->target)->initialize($this->target);
    }

    /**
     * Transforms the given source sale to the given target sale.
     *
     * @return \Ekyna\Component\Resource\Event\ResourceEventInterface|null The event that stopped transformation if any.
     *
     * @throws LogicException If initialize has not been called first.
     */
    public function transform()
    {
        if (null === $this->source || null === $this->target) {
            throw new LogicException("Please call initialize first.");
        }

        $this->preTransform(); // TODO Use event

        // Persist the target sale
        $event = $this->getOperator($this->target)->persist($this->target);
        if (!$event->isPropagationStopped() && !$event->hasErrors()) {
            // Disable the uploadable listener
            $this->uploadableListener->setEnabled(false);

            // Delete the source sale
            $sourceEvent = $this->getOperator($this->source)->delete($this->source, true); // Hard delete
            if (!$sourceEvent->isPropagationStopped() && !$sourceEvent->hasErrors()) {
                $event = null;
            }

            // Enable the uploadable listener
            $this->uploadableListener->setEnabled(true);
        }

        $this->postTransform(); // TODO Use event

        // Unset source and target sales
        $this->source = null;
        $this->target = null;

        return $event;
    }

    /**
     * Post copy handler.
     */
    protected function postCopy()
    {
        // Origin number
        if ($this->source instanceof QuoteInterface || $this->source instanceof OrderInterface) {
            $this->target->setOriginNumber($this->source->getNumber());
        }

        // Abort if source sale has no customer
        if (null === $customer = $this->source->getCustomer()) {
            return;
        }

        // If target sale is order and source customer has parent
        if ($this->target instanceof OrderInterface && $customer->hasParent()) {
            // Sets the parent as customer
            $this->target->setCustomer($customer->getParent());
        }
    }

    /**
     * Pre transform handler.
     */
    protected function preTransform()
    {
        // Abort if source sale has no customer
        if (null === $customer = $this->source->getCustomer()) {
            return;
        }

        // Order specific: origin customer
        if ($this->target instanceof OrderInterface) {
            // If target sale has no origin customer
            if (null === $this->target->getOriginCustomer()) {
                // If the source customer is different from the target sale's customer
                if ($customer !== $this->target->getCustomer()) {
                    // Set origin customer
                    $this->target->setOriginCustomer($customer);
                }
            }
        }
    }

    /**
     * Post transform handler.
     */
    protected function postTransform()
    {

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
