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
    public function __construct($email = null, $name = null, $type = null, UserInterface $user = null)
    {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
        $this->user = $user;
    }

//    /**
//     * Returns the string representation.
//     *
//     * @return string
//     */
//    public function __toString()
//    {
//        return $this->getChoiceLabel();
//    }
//
//    /**
//     * Returns the choice label.
//     *
//     * @return string
//     */
//    public function getChoiceLabel()
//    {
//        $label = '';
//
//        if (!empty($this->type)) {
//            $label = '[' . $this->type . '] ';
//        }
//
//        if (!empty($this->name)) {
//            $label .= sprintf('%s &lt;%s&gt;', $this->name, $this->email);
//        } else {
//            $label .= $this->email;
//        }
//
//        return $label;
//    }

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return Recipient
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail()
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
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
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
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the role.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser()
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
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
