<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;
use libphonenumber\PhoneNumber;

/**
 * Interface CustomerContactInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerContactInterface
    extends ResourceInterface,
            IdentityInterface,
            NotificationsInterface,
            TimestampableInterface
{
    public function getCustomer(): ?CustomerInterface;

    /**
     * @return $this|CustomerContactInterface
     */
    public function setCustomer(?CustomerInterface $customer): CustomerContactInterface;

    public function getEmail(): ?string;

    /**
     * @return $this|CustomerContactInterface
     */
    public function setEmail(string $email): CustomerContactInterface;

    public function getTitle(): ?string;

    /**
     * @return $this|CustomerContactInterface
     */
    public function setTitle(?string $title): CustomerContactInterface;

    public function getPhone(): ?PhoneNumber;

    /**
     * @return $this|CustomerContactInterface
     */
    public function setPhone(?PhoneNumber $phone): CustomerContactInterface;

    public function getDescription(): ?string;

    /**
     * @return $this|CustomerContactInterface
     */
    public function setDescription(?string $description): CustomerContactInterface;
}
