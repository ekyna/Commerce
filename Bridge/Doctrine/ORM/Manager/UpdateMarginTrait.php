<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Model\MarginSubjectInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use Ekyna\Component\Resource\Model\ResourceInterface;

use function date;

/**
 * Trait UpdateMarginTrait
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait UpdateMarginTrait
{
    private ?Query $updateMarginQuery = null;

    /**
     * @param ResourceInterface&MarginSubjectInterface $subject
     * @return void
     */
    protected function updateMarginSubject(MarginSubjectInterface&ResourceInterface $subject): void
    {
        $margin = $subject->getMargin();

        $this->getUpdateMarginQuery()->execute([
            'revenue_product'  => $margin->getRevenueProduct()->toFixed(5),
            'revenue_shipment' => $margin->getRevenueShipment()->toFixed(5),
            'cost_product'     => $margin->getCostProduct()->toFixed(5),
            'cost_supply'      => $margin->getCostSupply()->toFixed(5),
            'cost_shipment'    => $margin->getCostShipment()->toFixed(5),
            'is_average'       => $margin->isAverage() ? 1 : 0,
            'date'             => date('Y-m-d H:i:s'),
            'id'               => $subject->getId(),
        ]);
    }

    /**
     * Returns the update margin query.
     *
     * @return Query
     */
    private function getUpdateMarginQuery(): Query
    {
        if ($this->updateMarginQuery) {
            return $this->updateMarginQuery;
        }

        if (!$this instanceof ResourceManager) {
            throw new UnexpectedTypeException($this, ResourceManager::class);
        }

        return $this->updateMarginQuery = $this->wrapped->createQuery(<<<DQL
            UPDATE $this->resourceClass o
            SET o.margin.revenueProduct = :revenue_product,
                o.margin.revenueShipment = :revenue_shipment,
                o.margin.costProduct = :cost_product,
                o.margin.costSupply = :cost_supply,
                o.margin.costShipment = :cost_shipment,
                o.margin.average = :is_average,
                o.updatedAt = :date
            WHERE o.id = :id
            DQL
        );
    }
}
