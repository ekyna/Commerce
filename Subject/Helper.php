<?php

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;

/**
 * Class Helper
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Helper implements HelperInterface
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    protected $registry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     */
    public function __construct(SubjectProviderRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(SaleItemInterface $item)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if ((null === $subject = $item->getSubject()) && $item->hasSubjectData()) {
            $subject = $this->getResolver($item)->resolve($item);
            /** @noinspection PhpInternalEntityUsedInspection */
            $item->setSubject($subject);

            return $subject;
        }

        return null;
    }

    /**
     * Returns the provider that supports the item.
     *
     * @param SaleItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface
     * @throws InvalidArgumentException
     */
    protected function getResolver(SaleItemInterface $item)
    {
        if (null === $provider = $this->registry->getProviderByItem($item)) {
            throw new InvalidArgumentException('Unsupported subject.');
        }

        return $provider;
    }
}
