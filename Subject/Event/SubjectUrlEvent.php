<?php

namespace Ekyna\Component\Commerce\Subject\Event;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SubjectUrlEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectUrlEvent extends Event
{
    const ADD_TO_CART    = 'ekyna_commerce.subject_url.add_to_cart';
    const RESUPPLY_ALERT = 'ekyna_commerce.subject_url.resupply_alert';
    const PUBLIC         = 'ekyna_commerce.subject_url.public';
    const IMAGE          = 'ekyna_commerce.subject_url.image';
    const PRIVATE        = 'ekyna_commerce.subject_url.private';

    /**
     * @var SubjectInterface
     */
    private $subject;

    /**
     * @var boolean
     */
    private $path;

    /**
     * @var string
     */
    private $url;


    /**
     * Constructor.
     *
     * @param SubjectInterface $subject
     * @param bool             $path
     */
    public function __construct(SubjectInterface $subject, bool $path = true)
    {
        $this->subject = $subject;
        $this->path    = $path;
    }

    /**
     * Returns the subject.
     *
     * @return SubjectInterface
     */
    public function getSubject(): SubjectInterface
    {
        return $this->subject;
    }

    /**
     * Returns whether to generate a path instead of an url.
     *
     * @return bool
     */
    public function isPath(): bool
    {
        return $this->path;
    }

    /**
     * Returns the url.
     *
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Sets the url.
     *
     * @param string $url
     *
     * @return SubjectUrlEvent
     */
    public function setUrl(string $url): SubjectUrlEvent
    {
        $this->url = $url;

        return $this;
    }
}
