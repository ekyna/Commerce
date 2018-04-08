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
        if (!($source->getCompany() === $target->getCompany()
            && $source->getGender() === $target->getGender()
            && $source->getFirstName() === $target->getFirstName()
            && $source->getLastName() === $target->getLastName()
            && $source->getStreet() === $target->getStreet()
            && $source->getComplement() === $target->getComplement()
            && $source->getSupplement() === $target->getSupplement()
            && $source->getCity() === $target->getCity()
            && $source->getPostalCode() === $target->getPostalCode())) {
            return false;
        }

        $sourceCountryId = $source->getCountry() ? $source->getCountry()->getId() : null;
        $targetCountryId = $target->getCountry() ? $target->getCountry()->getId() : null;
        if ($sourceCountryId != $targetCountryId) {
            return false;
        }

        $sourceStateId = $source->getState() ? $source->getState()->getId() : null;
        $targetStateId = $target->getState() ? $target->getState()->getId() : null;
        if ($sourceStateId != $targetStateId) {
            return false;
        }
        
        $sourcePhone = (string) $source->getPhone();
        $targetPhone = (string) $target->getPhone();
        if ($sourcePhone !== $targetPhone) {
            return false;
        }
        
        $sourceMobile = (string) $source->getMobile();
        $targetMobile = (string) $target->getMobile();
        if ($sourceMobile !== $targetMobile) {
            return false;
        }

        return true;
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
            ->setComplement($source->getComplement())
            ->setSupplement($source->getSupplement())
            ->setCity($source->getCity())
            ->setPostalCode($source->getPostalCode())
            ->setCountry($source->getCountry())
            ->setState($source->getState());

        if (is_object($phone = $source->getPhone())) {
            $target->setPhone(clone $phone);
        } else {
            $target->setPhone($phone);
        }

        if (is_object($mobile = $source->getMobile())) {
            $target->setMobile(clone $mobile);
        } else {
            $target->setMobile($mobile);
        }
    }
}
