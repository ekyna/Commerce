<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject;

use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface as SubjectProvider;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;

/**
 * Interface SubjectHelperInterface
 * @package Ekyna\Component\Commerce\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectHelperInterface
{
    /**
     * Returns whether or the subject reference has subject.
     */
    public function hasSubject(SubjectReferenceInterface $reference): bool;

    /**
     * Resolves the subject from the reference.
     *
     * @throws SubjectException
     */
    public function resolve(SubjectReferenceInterface $reference, bool $throw = true): ?SubjectInterface;

    /**
     * Assigns the subject to the reference.
     */
    public function assign(SubjectReferenceInterface $reference, SubjectInterface $subject): SubjectProvider;

    /**
     * Finds the subject by its provider and identifier.
     */
    public function find(string $provider, int $identifier): ?SubjectInterface;
}
