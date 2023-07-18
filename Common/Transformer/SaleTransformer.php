<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Bundle\ResourceBundle\Service\Uploader\UploadToggler;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class SaleTransformer
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer extends AbstractOperator implements SaleTransformerInterface
{
    protected readonly UploadToggler $uploadToggler;

    public function setUploadToggler(UploadToggler $uploadToggler): void
    {
        $this->uploadToggler = $uploadToggler;
    }

    public function initialize(SaleInterface $source, SaleInterface $target): ResourceEventInterface
    {
        $this->source = $source;
        $this->target = $target;

        $event = new SaleTransformEvent($this->source, $this->target);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::INIT_TRANSFORM);
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
        if ($targetEvent->hasErrors() || $targetEvent->isPropagationStopped()) {
            return $targetEvent;
        }

        $this->getManager($this->target)->refresh($this->target);

        // Disable the uploadable listener
        $this->uploadToggler->setEnabled(false);

        // Delete the source sale
        $sourceEvent = $this->getManager($this->source)->delete($this->source, true); // Hard delete
        foreach ($sourceEvent->getMessages() as $message) {
            if (ResourceMessage::TYPE_ERROR === $message->getType()) {
                $message->setType(ResourceMessage::TYPE_WARNING);
            }
            $event->addMessage($message);
        }

        // Enable the uploadable listener
        $this->uploadToggler->setEnabled(true);

        $this->eventDispatcher->dispatch($event, SaleTransformEvents::POST_TRANSFORM);
        if ($event->hasErrors() || $event->isPropagationStopped()) {
            return $event;
        }

        // Unset source and target sales
        $this->source = null;
        $this->target = null;

        return null;
    }
}
