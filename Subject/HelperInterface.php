<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface HelperInterface
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface HelperInterface
{
    /**
     * Returns the subject from the order item.
     *
     * @param OrderItemInterface $item
     * @return SubjectInterface|null
     * @throws InvalidArgumentException
     */
    public function resolve(OrderItemInterface $item);

    /**
     * Transforms the subject to an order item.
     *
     * @param SubjectInterface $subject
     * @return OrderItemInterface
     * @throws InvalidArgumentException
     */
    public function transform(SubjectInterface $subject);

    /**
     * Generates the front office path for the given subject or order item.
     *
     * @param SubjectInterface|OrderItemInterface $subjectOrItem
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function generateFrontOfficePath($subjectOrItem);

    /**
     * Generates the back office path for the given subject or order item.
     *
     * @param SubjectInterface|OrderItemInterface $subjectOrItem
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function generateBackOfficePath($subjectOrItem);
}
