<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\User\Model\UserInterface;

/**
 * Class Recipient
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Recipient
{
    public const TYPE_WEBSITE       = 'website';
    public const TYPE_USER          = 'user';
    public const TYPE_ADMINISTRATOR = 'administrator';
    public const TYPE_IN_CHARGE     = 'in_charge';
    public const TYPE_CUSTOMER      = 'customer';
    public const TYPE_CONTACT       = 'contact';
    public const TYPE_SALESMAN      = 'salesman';
    public const TYPE_ACCOUNTABLE   = 'accountable';
    public const TYPE_SUPPLIER      = 'supplier';


    public static function getTypes(): array
    {
        return [
            self::TYPE_WEBSITE,
            self::TYPE_USER,
            self::TYPE_ADMINISTRATOR,
            self::TYPE_IN_CHARGE,
            self::TYPE_CUSTOMER,
            self::TYPE_CONTACT,
            self::TYPE_SALESMAN,
            self::TYPE_ACCOUNTABLE,
            self::TYPE_SUPPLIER,
        ];
    }

    private string        $email;
    private string        $name;
    private ?string        $type;
    private ?UserInterface $user;


    public function __construct(
        string $email,
        string $name = '',
        string $type = null,
        UserInterface $user = null
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
        $this->user = $user;
    }

    public function setEmail(string $email): Recipient
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setName(string $name): Recipient
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setType(?string $type): Recipient
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user = null): Recipient
    {
        $this->user = $user;

        return $this;
    }
}
