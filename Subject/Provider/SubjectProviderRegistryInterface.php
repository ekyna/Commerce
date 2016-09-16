<?php

namespace Ekyna\Component\Commerce\Subject\Provider;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface SubjectProviderRegistryInterface
 * @package Ekyna\Component\Commerce\Subject\Provider
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubjectProviderRegistryInterface
{
    /**
     * Adds the subject provider.
     *
     * @param SubjectProviderInterface $provider
     */
    public function addProvider(SubjectProviderInterface $provider);

    /**
     * Returns the provider by name or sale item.
     *
     * @param string|SaleItemInterface|object $nameOrItemOrSubjectOrSubject
     *
     * @return SubjectProviderInterface|null
     */
    public function getProvider($nameOrItemOrSubjectOrSubject);

    /**
     * Returns the provider supporting the item.
     *
     * @param SaleItemInterface $item
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderByItem(SaleItemInterface $item);

    /**
     * Returns the provider supporting the subject.
     *
     * @param object $subject
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderBySubject($subject);

    /**
     * Returns the provider by name.
     *
     * @param string $name
     *
     * @return SubjectProviderInterface|null
     */
    public function getProviderByName($name);

    /**
     * Resolves the item's subject.
     *
     * @param SaleItemInterface $item
     *
     * @return mixed|null
     */
    public function resolveItemSubject(SaleItemInterface $item);

    /**
     * Returns the providers.
     *
     * @return array|SubjectProviderInterface[]
     */
    public function getProviders();
}
