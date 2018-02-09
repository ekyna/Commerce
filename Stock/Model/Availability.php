<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Class Availability
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Availability
{
    /**
     * @var string
     */
    private $overflowMessage;

    /**
     * @var float
     */
    private $minimumQuantity;

    /**
     * @var string
     */
    private $minimumMessage;

    /**
     * @var float
     */
    private $maximumQuantity;

    /**
     * @var string
     */
    private $maximumMessage;

    /**
     * @var float
     */
    private $availableQuantity;

    /**
     * @var string
     */
    private $availableMessage;

    /**
     * @var float
     */
    private $resupplyQuantity;

    /**
     * @var string
     */
    private $resupplyMessage;


    /**
     * Constructor.
     *
     * @param string $overflowMessage
     * @param float  $minimumQuantity
     * @param string $minimumMessage
     * @param float  $maximumQuantity
     * @param string $maximumMessage
     * @param float  $availableQuantity
     * @param string $availableMessage
     * @param float  $resupplyQuantity
     * @param string $resupplyMessage
     */
    public function __construct(
        string $overflowMessage,
        float $minimumQuantity = 0,
        string $minimumMessage = null,
        float $maximumQuantity = 0,
        string $maximumMessage = null,
        float $availableQuantity,
        string $availableMessage = null,
        float $resupplyQuantity = 0,
        string $resupplyMessage = null
    ) {
        $this->overflowMessage = $overflowMessage;
        $this->minimumQuantity = $minimumQuantity;
        $this->minimumMessage = $minimumMessage;
        $this->maximumQuantity = $maximumQuantity;
        $this->maximumMessage = $maximumMessage;
        $this->availableQuantity = $availableQuantity;
        $this->availableMessage = $availableMessage;
        $this->resupplyQuantity = $resupplyQuantity;
        $this->resupplyMessage = $resupplyMessage;
    }

    /**
     * Returns the messages for the given quantity.
     *
     * @param float $quantity
     *
     * @return array
     */
    public function getMessagesForQuantity(float $quantity)
    {
        $messages = [];

        if ($quantity < $this->minimumQuantity) {
            $messages[] = $this->minimumMessage;
        } elseif (0 < $this->maximumQuantity && $quantity > $this->maximumQuantity) {
            $messages[] = $this->maximumMessage;
        } else {
            if (null !== $this->availableMessage) {
                $messages[] = $this->availableMessage;
            }

            if ($quantity > $this->availableQuantity) {
                if (null !== $this->resupplyMessage) {
                    $messages[] = $this->resupplyMessage;
                    if ($quantity > $this->availableQuantity + $this->resupplyQuantity) {
                        $messages[] = $this->overflowMessage;
                    }
                } else {
                    $messages[] = $this->overflowMessage;
                }
            }
        }

        if (empty($messages)) {
            $messages[] = $this->overflowMessage;
        }

        return $messages;
    }

    /**
     * Returns
     *
     * @param float $quantity
     *
     * @return bool
     */
    public function isAvailableForQuantity(float $quantity)
    {
        if ($quantity < $this->minimumQuantity) {
            return false;
        } elseif ($quantity > $this->availableQuantity + $this->resupplyQuantity) {
            return false;
        }

        return true;
    }

    /**
     * Returns the overflow message.
     *
     * @return string
     */
    public function getOverflowMessage()
    {
        return $this->overflowMessage;
    }

    /**
     * Returns the minimum quantity.
     *
     * @return float
     */
    public function getMinimumQuantity()
    {
        return $this->minimumQuantity;
    }

    /**
     * Returns the minimum message.
     *
     * @return string
     */
    public function getMinimumMessage()
    {
        return $this->minimumMessage;
    }

    /**
     * Returns the maximum quantity.
     *
     * @return float
     */
    public function getMaximumQuantity()
    {
        return $this->maximumQuantity;
    }

    /**
     * Returns the maximum message.
     *
     * @return string
     */
    public function getMaximumMessage()
    {
        return $this->maximumMessage;
    }

    /**
     * Returns the available quantity.
     *
     * @return float
     */
    public function getAvailableQuantity()
    {
        return $this->availableQuantity;
    }

    /**
     * Returns the available message.
     *
     * @return string
     */
    public function getAvailableMessage()
    {
        return $this->availableMessage;
    }

    /**
     * Returns the resupply quantity.
     *
     * @return float
     */
    public function getResupplyQuantity()
    {
        return $this->resupplyQuantity;
    }

    /**
     * Returns the resupply message.
     *
     * @return string
     */
    public function getResupplyMessage()
    {
        return $this->resupplyMessage;
    }

    /**
     * Returns the array version.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'o_msg'   => $this->overflowMessage,
            'min_qty' => $this->minimumQuantity,
            'min_msg' => $this->minimumMessage,
            'max_qty' => INF === $this->maximumQuantity ? 'INF' : $this->maximumQuantity,
            'max_msg' => $this->maximumMessage,
            'a_qty'   => INF === $this->availableQuantity ? 'INF' : $this->availableQuantity,
            'a_msg'   => $this->availableMessage,
            'r_qty'   => $this->resupplyQuantity,
            'r_msg'   => $this->resupplyMessage,
        ];
    }
}