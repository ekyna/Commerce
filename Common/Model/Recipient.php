<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Class Recipient
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Recipient
{
    const TYPE_WEBSITE       = 'website';
    const TYPE_USER          = 'user';
    const TYPE_ADMINISTRATOR = 'administrator';
    const TYPE_IN_CHARGE     = 'in_charge';
    const TYPE_CUSTOMER      = 'customer';
    const TYPE_CONTACT       = 'contact';
    const TYPE_SALESMAN      = 'salesman';
    const TYPE_ACCOUNTABLE   = 'accountable';
    const TYPE_SUPPLIER      = 'supplier';


    /**
     * Returns the valid types.
     *
     * @return array
     */
    public static function getTypes()
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

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var UserInterface
     */
    private $user;


    /**
     * Constructor.
     *
     * @param string        $email
     * @param string        $name
     * @param string        $type
     * @param UserInterface $user
     */
    public function __construct(
        string $email = null,
        string $name = null,
        string $type = null,
        UserInterface $user = null
    ) {
        $this->email = $email;
        $this->name  = $name;
        $this->type  = $type;
        $this->user  = $user;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return Recipient
     */
    public function setEmail(string $email = null): Recipient
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the email.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return Recipient
     */
    public function setName(string $name = null): Recipient
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return Recipient
     */
    public function setType(string $type = null): Recipient
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the role.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return Recipient
     */
    public function setUser(UserInterface $user = null): Recipient
    {
        $this->user = $user;

        return $this;
    }
}
