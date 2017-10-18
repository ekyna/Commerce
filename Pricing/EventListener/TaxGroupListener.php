<?php

namespace Ekyna\Component\Commerce\Pricing\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class TaxGroupListener
 * @package Ekyna\Component\Commerce\Pricing\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var TaxGroupRepositoryInterface
     */
    protected $taxGroupRepository;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface  $persistenceHelper
     * @param TaxGroupRepositoryInterface $taxGroupRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        TaxGroupRepositoryInterface $taxGroupRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->taxGroupRepository = $taxGroupRepository;
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $taxGroup = $this->getTaxGroupFromEvent($event);

        if ($taxGroup->isDefault()) {
            throw new IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $taxGroup = $this->getTaxGroupFromEvent($event);

        $this->fixDefault($taxGroup);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $taxGroup = $this->getTaxGroupFromEvent($event);

        $this->fixDefault($taxGroup);
    }

    /**
     * Fixes the default tax group.
     *
     * @param TaxGroupInterface $taxGroup
     */
    protected function fixDefault(TaxGroupInterface $taxGroup)
    {
        if (!$this->persistenceHelper->isChanged($taxGroup, ['default'])) {
            return;
        }

        if ($taxGroup->isDefault()) {
            $previousTaxGroup = $this->taxGroupRepository->findDefault();
            if ($previousTaxGroup === $taxGroup) {
                return;
            }

            $previousTaxGroup->setDefault(false);

            $this->persistenceHelper->persistAndRecompute($previousTaxGroup, false);
        }
    }

    /**
     * Returns the tax group from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return TaxGroupInterface
     * @throws InvalidArgumentException
     */
    protected function getTaxGroupFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof TaxGroupInterface) {
            throw new InvalidArgumentException('Expected instance of ' . TaxGroupInterface::class);
        }

        return $resource;
    }
}
