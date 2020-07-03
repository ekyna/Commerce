<?php

namespace Ekyna\Component\Commerce\Customer\Loyalty;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Commerce\Features;

/**
 * Class CouponGenerator
 * @package Ekyna\Component\Commerce\Customer\Loyalty
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CouponGenerator
{
    /**
     * @var Features
     */
    private $features;

    /**
     * @var LoyaltyUpdater
     */
    private $updater;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var array
     */
    private $generated;


    /**
     * Constructor.
     *
     * @param Features                    $features
     * @param LoyaltyUpdater              $updater
     * @param CustomerRepositoryInterface $customerRepository
     * @param CouponRepositoryInterface   $couponRepository
     * @param EntityManagerInterface      $manager
     */
    public function __construct(
        Features $features,
        LoyaltyUpdater $updater,
        CustomerRepositoryInterface $customerRepository,
        CouponRepositoryInterface $couponRepository,
        EntityManagerInterface $manager
    ) {
        $this->features = $features;
        $this->updater = $updater;
        $this->customerRepository = $customerRepository;
        $this->couponRepository = $couponRepository;
        $this->manager = $manager;
    }

    /**
     * Generates coupons regarding to customers loyalty point amounts.
     *
     * @return array The generated coupons.
     */
    public function generate(): array
    {
        $config = $this->features->getConfig(Features::LOYALTY . '.coupons');

        if (empty($config)) {
            return [];
        }

        $this->generated = [];

        krsort($config);

        foreach ($config as $points => $data) {
            $this->generateForPointsWithConfig($points, $data);
        }

        return $this->generated;
    }

    /**
     * Generates coupons for customer having minimum loyalty points.
     *
     * @param int   $points
     * @param array $config
     */
    private function generateForPointsWithConfig(int $points, array $config): void
    {
        $customers = $this->customerRepository->findWithLoyaltyPoints($points);

        if (empty($customers)) {
            return;
        }

        foreach ($customers as $customer) {
            while ($points <= $customer->getLoyaltyPoints()) {
                $coupon = $this->createCoupon($customer, $config);

                $this->updater->remove(
                    $customer,
                    $config['final'] ? $customer->getLoyaltyPoints() : $points,
                    'Coupon ' . $coupon->getCode()
                );

                if (!isset($this->generated[$customer->getId()])) {
                    $this->generated[$customer->getId()] = [
                        'customer' => $customer,
                        'coupons'  => [],
                    ];
                }

                $this->generated[$customer->getId()]['coupons'][] = $coupon;
            }
        }

        $this->manager->flush();
        $this->manager->clear();
    }

    /**
     * Creates coupon for the given customer.
     *
     * @param CustomerInterface $customer
     * @param array             $config
     *
     * @return CouponInterface
     */
    private function createCoupon(CustomerInterface $customer, array $config): CouponInterface
    {
        $start = (new \DateTime())->setTime(0, 0, 0, 0);
        $end = (new \DateTime($config['period']))->setTime(23, 59, 59, 999999);

        $t = \Transliterator::createFromRules(
            ':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
            \Transliterator::FORWARD
        );

        $firstName = preg_replace('~[^A-Za-z]+~', '', $t->transliterate($customer->getFirstName()));
        $lastName = preg_replace('~[^A-Za-z]+~', '', $t->transliterate($customer->getLastName()));

        $prefix = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 3));
        do {
            $code = $prefix . '-' . strtoupper(bin2hex(random_bytes(2)));
        } while (null !== $this->couponRepository->findOneByCode($code));

        /** @var CouponInterface $coupon */
        $coupon = $this->couponRepository->createNew();
        $coupon
            ->setCustomer($customer)
            ->setMode($config['mode'])
            ->setAmount($config['amount'])
            ->setCumulative(false)
            ->setMinGross(0)
            ->setLimit(1)
            ->setStartAt($start)
            ->setEndAt($end)
            ->setCode($code);

        $this->manager->persist($coupon);

        return $coupon;
    }
}
