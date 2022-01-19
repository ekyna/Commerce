<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploadToggler;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


/**
 * Class SaleTransformer
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer implements SaleTransformerInterface
{
    private SaleCopierFactoryInterface $saleCopierFactory;
    private FactoryFactoryInterface    $factoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private UploadToggler              $uploadToggler;
    private EventDispatcherInterface   $eventDispatcher;

    protected ?SaleInterface $source = null;
    protected ?SaleInterface $target = null;


    public function __construct(
        SaleCopierFactoryInterface $saleCopierFactory,
        FactoryFactoryInterface    $factoryFactory,
        ManagerFactoryInterface    $managerFactory,
        UploadToggler              $uploadToggler,
        EventDispatcherInterface   $eventDispatcher
    ) {
        $this->saleCopierFactory = $saleCopierFactory;
        $this->factoryFactory = $factoryFactory;
        $this->managerFactory = $managerFactory;
        $this->uploadToggler = $uploadToggler;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function initialize(SaleInterface $source, SaleInterface $target): ResourceEventInterface
    {
        $this->source = $source;
        $this->target = $target;

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::PRE_COPY);
        if ($event->isPropagationStopped()) {
            return $event;
        }

        $this
            ->saleCopierFactory
            ->create($this->source, $this->target)
            ->copySale();

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::POST_COPY);

        $this->getFactory($this->target)->initialize($this->target);

        return $event;
    }

    public function transform(): ?ResourceEventInterface
    {
        if (null === $this->source || null === $this->target) {
            throw new LogicException('Please call initialize first.');
        }

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::PRE_TRANSFORM);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Persist the target sale
        $targetEvent = $this->getManager($this->target)->save($this->target);
        if (!$targetEvent->isPropagationStopped() && !$targetEvent->hasErrors()) {
            $this->getManager($this->target)->refresh($this->target);

            // Disable the uploadable listener
            $this->uploadToggler->setEnabled(false);

            // Delete the source sale
            $sourceEvent = $this->getManager($this->source)->delete($this->source, true); // Hard delete
            if (!$sourceEvent->isPropagationStopped() && !$sourceEvent->hasErrors()) {
                $targetEvent = null;
            }

            // Enable the uploadable listener
            $this->uploadToggler->setEnabled(true);
        }

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::POST_TRANSFORM);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Unset source and target sales
        $this->source = null;
        $this->target = null;

        return $targetEvent;
    }

    /**
     * Returns the factory for the given sale.
     */
    protected function getFactory(SaleInterface $sale): SaleFactoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->factoryFactory->getFactory(
            $this->resolveInterface($sale)
        );
    }

    /**
     * Returns the manager for the given sale.
     */
    protected function getManager(SaleInterface $sale): ResourceManagerInterface
    {
        return $this->managerFactory->getManager(
            $this->resolveInterface($sale)
        );
    }

    private function resolveInterface(SaleInterface $sale): string
    {
        $interfaces = [CartInterface::class, QuoteInterface::class, OrderInterface::class];

        foreach ($interfaces as $interface) {
            if (!$sale instanceof $interface) {
                continue;
            }

            return $interface;
        }

        throw new UnexpectedTypeException($sale, $interfaces);
    }
}
