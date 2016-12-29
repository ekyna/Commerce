<?php

namespace Ekyna\Component\Commerce\Common\Util;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;

/**
 * Class AddressUtil
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AddressUtil
{
    /**
     * Returns whether this address equals the given address or not.
     *
     * @param AddressInterface $source
     * @param AddressInterface $target
     *
     * @return boolean
     */
    static public function equals(AddressInterface $source, AddressInterface $target)
    {
        return $source->getCompany() === $target->getCompany()
            && $source->getGender() === $target->getGender()
            && $source->getFirstName() === $target->getFirstName()
            && $source->getLastName() === $target->getLastName()
            && $source->getStreet() === $target->getStreet()
            && $source->getSupplement() === $target->getSupplement()
            && $source->getCity() === $target->getCity()
            && $source->getPostalCode() === $target->getPostalCode()
            && $source->getCountry() === $target->getCountry()
            && $source->getState() === $target->getState()
            && $source->getPhone() === $target->getPhone()
            && $source->getMobile() === $target->getMobile();
    }

    /**
     * Copy the source address data into the target address.
     *
     * @param AddressInterface $source
     * @param AddressInterface $target
     */
    static public function copy(AddressInterface $source, AddressInterface $target)
    {
        $target
            ->setCompany($source->getCompany())
            ->setGender($source->getGender())
            ->setFirstName($source->getFirstName())
            ->setLastName($source->getLastName())
            ->setStreet($source->getStreet())
            ->setSupplement($source->getSupplement())
            ->setCity($source->getCity())
            ->setPostalCode($source->getPostalCode())
            ->setCountry($source->getCountry())
            ->setState($source->getState())
            ->setPhone($source->getPhone())
            ->setMobile($source->getMobile());
    }
}
