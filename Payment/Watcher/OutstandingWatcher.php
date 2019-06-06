<?php

namespace Ekyna\Component\Commerce\Payment\Watcher;

use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants;
use Ekyna\Component\Commerce\Payment\Model;
use Ekyna\Component\Commerce\Payment\Repository;

/**
 * Class OutstandingWatcher
 * @package Ekyna\Component\Commerce\Payment\Watcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class OutstandingWatcher implements WatcherInterface
{
    /**
     * @var Repository\PaymentTermRepositoryInterface
     */
    private $termRepository;

    /**
     * @var Repository\PaymentMethodRepositoryInterface
     */
    private $methodRepository;


    /**
     * Constructor.
     *
     * @param Repository\PaymentTermRepositoryInterface $termRepository
     * @param Repository\PaymentMethodRepositoryInterface $methodRepository
     */
    public function __construct(
        Repository\PaymentTermRepositoryInterface $termRepository,
        Repository\PaymentMethodRepositoryInterface $methodRepository
    ) {
        $this->termRepository = $termRepository;
        $this->methodRepository = $methodRepository;
    }

    /**
     * Watch for outstanding payments.
     *
     * @param Repository\PaymentRepositoryInterface $paymentRepository
     *
     * @return bool Whether some payments have been updated.
     */
    public function watch(Repository\PaymentRepositoryInterface $paymentRepository)
    {
        if (null === $term = $this->termRepository->findLongest()) {
            return false;
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0, 0);

        $fromDate = clone $today;
        $fromDate->modify('-1 year');

        $states = [Model\PaymentStates::STATE_AUTHORIZED, Model\PaymentStates::STATE_CAPTURED];

        /** @var Model\PaymentMethodInterface $method */
        $method = $this->methodRepository->findOneBy([
            'factoryName' => Constants::FACTORY_NAME,
        ]);

        if (!$method || !$method->isOutstanding()) {
            return false;
        }

        $result = false;
        $payments = $paymentRepository->findByMethodAndStates($method, $states, $fromDate);

        foreach ($payments as $payment) {
            $sale = $payment->getSale();

            // Sale may not have a outstanding limit date
            if (null === $date = $sale->getOutstandingDate()) {
                continue;
            }

            // If outstanding limit date is past
            $diff = $date->diff($today);
            if (0 < $diff->days && !$diff->invert) {
                $payment->setState(Model\PaymentStates::STATE_EXPIRED);

                $this->persist($payment);

                $result = true;
            }
        }

        return $result;
    }

    /**
     * Persists the payment.
     *
     * @param Model\PaymentInterface $payment
     */
    abstract protected function persist(Model\PaymentInterface $payment);
}
