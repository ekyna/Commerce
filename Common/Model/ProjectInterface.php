<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;


use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Project
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ProjectInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): ProjectInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): ProjectInterface;

    public function getOrders(): Collection;

    public function addOrder(OrderInterface $order): ProjectInterface;

    public function removeOrder(OrderInterface $order): ProjectInterface;

    public function getQuotes(): Collection;

    public function addQuote(QuoteInterface $quote): ProjectInterface;

    public function removeQuote(QuoteInterface $quote): ProjectInterface;
}
