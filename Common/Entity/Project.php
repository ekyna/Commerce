<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\ProjectInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class Project
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Project extends AbstractResource implements ProjectInterface
{
    private ?string $name = null;
    private ?string $description = null;

    private Collection $orders;
    private Collection $quotes;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->quotes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? 'New project';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): ProjectInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): ProjectInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(OrderInterface $order): ProjectInterface
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setProject($this);
        }

        return $this;
    }

    public function removeOrder(OrderInterface $order): ProjectInterface
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            $order->setProject(null);
        }

        return $this;
    }

    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(QuoteInterface $quote): ProjectInterface
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
            $quote->setProject($this);
        }

        return $this;
    }

    public function removeQuote(QuoteInterface $quote): ProjectInterface
    {
        if ($this->quotes->contains($quote)) {
            $this->quotes->removeElement($quote);
            $quote->setProject(null);
        }

        return $this;
    }
}
