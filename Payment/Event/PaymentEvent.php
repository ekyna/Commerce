<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Event;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PaymentEvent
 * @package Ekyna\Component\Commerce\Payment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentEvent extends Event
{
    private PaymentInterface $payment;
    private ?Response        $response = null;


    public function __construct(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    public function setPayment(PaymentInterface $payment): void
    {
        $this->payment = $payment;
    }

    public function getPayment(): PaymentInterface
    {
        return $this->payment;
    }

    public function setResponse(Response $response = null): void
    {
        $this->response = $response;
    }

    public function hasResponse(): bool
    {
        return null !== $this->response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
