<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\EventListener;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class BillOfMaterialsListener
 * @package Ekyna\Component\Commerce\Manufacture\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsListener
{
    public function __construct(
        private readonly GeneratorInterface         $numberGenerator,
        private readonly PersistenceHelperInterface $persistenceHelper,
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $bom = $this->getBOMFromEvent($event);

        if (!$this->updateNumber($bom)) {
            return;
        }

        $this->persistenceHelper->persistAndRecompute($bom, false);
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $bom = $this->getBOMFromEvent($event);

        if ($bom->getState() === BOMState::DRAFT) {
            return;
        }

        throw new IllegalOperationException(
            'Bill of materials with this state cannot be deleted'
        );
    }

    private function updateNumber(BillOfMaterialsInterface $bom): bool
    {
        if (!empty($bom->getNumber())) {
            return false;
        }

        $bom->setNumber($this->numberGenerator->generate($bom));

        return true;
    }

    protected function getBOMFromEvent(ResourceEventInterface $event): BillOfMaterialsInterface
    {
        $bom = $event->getResource();

        if (!$bom instanceof BillOfMaterialsInterface) {
            throw new UnexpectedTypeException($bom, BillOfMaterialsInterface::class);
        }

        return $bom;
    }
}
