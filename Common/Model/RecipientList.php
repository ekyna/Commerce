<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class RecipientList
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientList
{
    /** @var Recipient[]  */
    private array $recipients = [];


    public function add(Recipient $recipient): RecipientList
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
     * @return Recipient[]
     */
    public function all(): array
    {
        return $this->recipients;
    }
}
