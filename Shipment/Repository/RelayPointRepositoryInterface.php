<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

/**
 * Interface RelayPointRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RelayPointRepositoryInterface
{
    /**
     * Finds the relay point by its number and platform name.
     *
     * @param string $number
     * @param string $platform
     *
     * @return \Ekyna\Component\Commerce\Shipment\Entity\RelayPoint|null
     */
    public function findOneByNumberAndPlatform(string $number, string $platform);
}