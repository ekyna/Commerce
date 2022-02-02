<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use DateTime;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceTrait;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class ResupplyAlert
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlert extends AbstractResource implements SubjectReferenceInterface
{
    use SubjectReferenceTrait;

    /**
     * @var string
     */
    private $email;

    /**
     * @var DateTime
     */
    private $createdAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->initializeSubjectIdentity();
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
     * Sets the email.
     *
     * @param string $email
     *
     * @return ResupplyAlert
     */
    public function setEmail(string $email): ResupplyAlert
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the 'created at' date time.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the 'created at' date time.
     *
     * @param DateTime $createdAt
     *
     * @return ResupplyAlert
     */
    public function setCreatedAt(DateTime $createdAt): ResupplyAlert
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
