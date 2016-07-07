<?php

namespace Ekyna\Component\Commerce\Subject\Resolver;

use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface SubjectResolverInterface
 * @package Ekyna\Component\Commerce\Subject\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectResolverInterface
{
    /**
     * Returns the subject from the order item.
     *
     * @param OrderItemInterface $item
     *
     * @return SubjectInterface|null
     */
    public function resolve(OrderItemInterface $item);

    /**
     * Transforms the subject to an order item.
     *
     * @param SubjectInterface $subject
     *
     * @return OrderItemInterface
     */
    public function transform(SubjectInterface $subject);

    /**
     * Generates the front office path for the given subject or order item.
     *
     * @param SubjectInterface|OrderItemInterface $subjectOrItem
     *
     * @return string
     */
    public function generateFrontOfficePath($subjectOrItem);

    /**
     * Generates the back office path for the given subject or order item.
     *
     * @param SubjectInterface|OrderItemInterface $subjectOrItem
     *
     * @return string
     */
    public function generateBackOfficePath($subjectOrItem);

    /**
     * Returns whether the resolver supports the given subject or order item.
     *
     * @param SubjectInterface|OrderItemInterface $subjectOrItem
     *
     * @return boolean
     */
    public function supports($subjectOrItem);

    /**
     * Returns the resolver name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the order item class.
     *
     * @param string $class
     *
     * @return AbstractSubjectResolver
     */
    public function setItemClass($class);
}
