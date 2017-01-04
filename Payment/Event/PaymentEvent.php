<?php

namespace Ekyna\Component\Commerce\Payment\Event;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaymentEvent
 * @package Ekyna\Component\Commerce\Payment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentEvent extends Event
{
    /**
     * @var PaymentInterface
     */
    private $payment;

    /**
     * @var Response
     */
    private $response;


    /**
     * Constructor.
     *
     * @param PaymentInterface $payment
     */
    public function __construct(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Sets the payment.
     *
     * @param PaymentInterface $payment
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Returns the payment.
     *
     * @return PaymentInterface
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Sets the response.
     *
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Returns whether or not the event has a response.
     *
     * @return bool
     */
    public function hasResponse()
    {
        return null !== $this->response;
    }

    /**
     * Returns the response.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
