<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface PaymentTermInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentTermInterface extends TranslatableInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|PaymentTermInterface
     */
    public function setName($name);

    /**
     * Returns the days.
     *
     * @return int
     */
    public function getDays();

    /**
     * Sets the days.
     *
     * @param int $days
     *
     * @return $this|PaymentTermInterface
     */
    public function setDays($days);

    /**
     * Returns the end of month.
     *
     * @return boolean
     */
    public function getEndOfMonth();

    /**
     * Sets the end of month.
     *
     * @param boolean $endOfMonth
     *
     * @return $this|PaymentTermInterface
     */
    public function setEndOfMonth($endOfMonth);

    /**
     * Returns the title (translation alias).
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title (translation alias).
     *
     * @param string $title
     *
     * @return $this|PaymentTermInterface
     */
    public function setTitle($title);

    /**
     * Returns the description (translation alias).
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description (translation alias).
     *
     * @param string $description
     *
     * @return $this|PaymentTermInterface
     */
    public function setDescription($description);
}
