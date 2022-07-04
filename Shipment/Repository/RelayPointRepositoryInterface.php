<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Shipment\Model\RelayPointInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface RelayPointRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<RelayPointRepositoryInterface>
 */
interface RelayPointRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the relay point by its number and platform name.
     */
    public function findOneByNumberAndPlatform(string $number, string $platform): ?RelayPointInterface;
}
