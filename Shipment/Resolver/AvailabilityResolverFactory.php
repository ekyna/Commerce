<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class AvailabilityResolverFactory
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AvailabilityResolverFactory
{
    private ShipmentSubjectCalculatorInterface $calculator;
    private SubjectHelperInterface $subjectHelper;

    public function __construct(ShipmentSubjectCalculatorInterface $calculator, SubjectHelperInterface $subjectHelper)
    {
        $this->calculator = $calculator;
        $this->subjectHelper = $subjectHelper;
    }

    public function create(): AvailabilityResolver
    {
        return new AvailabilityResolver(
            $this->calculator,
            $this->subjectHelper,
            null
        );
    }

    public function createWithShipment(ShipmentInterface $shipment): AvailabilityResolver
    {
        return new AvailabilityResolver(
            $this->calculator,
            $this->subjectHelper,
            $shipment
        );
    }
}
