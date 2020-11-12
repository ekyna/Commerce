<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\ResupplyAlert;
use Ekyna\Component\Commerce\Stock\Repository\ResupplyAlertRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class ResupplyAlertHelper
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertHelper
{
    /**
     * @var ResupplyAlertRepositoryInterface
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var SubjectHelperInterface
     */
    protected $helper;


    /**
     * Constructor.
     *
     * @param ResupplyAlertRepositoryInterface $repository
     * @param EntityManagerInterface           $manager
     * @param SubjectHelperInterface           $helper
     */
    public function __construct(
        ResupplyAlertRepositoryInterface $repository,
        EntityManagerInterface $manager,
        SubjectHelperInterface $helper
    ) {
        $this->repository = $repository;
        $this->manager    = $manager;
        $this->helper     = $helper;
    }

    /**
     * Subscribes the given email to the given subject resupply alert.
     *
     * @param string           $email
     * @param SubjectInterface $subject
     *
     * @return bool TRUE if subscribed, FALSE if subscription found
     */
    public function subscribe(string $email, SubjectInterface $subject): bool
    {
        if (null !== $this->repository->findByEmailAndSubject($email, $subject)) {
            return false;
        }

        $alert = new ResupplyAlert();
        $alert->setEmail($email);

        $this->helper->assign($alert, $subject);

        $this->manager->persist($alert);
        $this->manager->flush();

        return true;
    }

    /**
     * Subscribes the given email to the given subject resupply alert.
     *
     * @param string           $email
     * @param SubjectInterface $subject
     *
     * @return bool TRUE if unsubscribed, FALSE if subscription not found
     */
    public function unsubscribe(string $email, SubjectInterface $subject): bool
    {
        if (null === $alert = $this->repository->findByEmailAndSubject($email, $subject)) {
            return false;
        }

        $this->manager->remove($alert);
        $this->manager->flush();

        return true;
    }
}
