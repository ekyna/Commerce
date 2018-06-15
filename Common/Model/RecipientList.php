<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class RecipientList
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientList
{
    /**
     * @var Recipient[]
     */
    private $recipients = [];


    /**
     * Adds the recipient.
     *
     * @param Recipient $recipient
     *
     * @return $this|RecipientList
     */
    public function add(Recipient $recipient)
    {
        foreach ($this->recipients as $r) {
            if ($r->getEmail() === $recipient->getEmail()) {
                $r
                    ->setType($recipient->getType())
                    ->setName($recipient->getName());

                return $this;
            }
        }

        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * Returns the recipients.
     *
     * @return Recipient[]
     */
    public function all()
    {
        return $this->recipients;
    }
}