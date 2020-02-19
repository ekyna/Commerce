<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

use DrewM\MailChimp\MailChimp;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Api
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Api extends MailChimp
{
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param string          $apiKey
     */
    public function __construct(LoggerInterface $logger, string $apiKey)
    {
        parent::__construct($apiKey);

        $this->logger = $logger;
    }

    /**
     * Returns the audiences.
     *
     * @param int $count
     * @param int $offset
     *
     * @return array
     */
    public function getAudiences(int $count = 10, int $offset = 0): array
    {
        $results = $this->get("lists", ['count' => $count, 'offset' => $offset]);

        if (!$this->success()) {
            $this->logError($results);

            throw new NewsletterException("Failed to retrieve audiences.");
        }

        return $results['lists'];
    }

    /**
     * Returns the list's audiences.
     *
     * @param AudienceInterface $audience
     * @param int               $count
     * @param int               $offset
     *
     * @return array
     */
    public function getAudienceMembers(AudienceInterface $audience, int $count = 10, int $offset = 0): array
    {
        $results = $this->get(
            "lists/{$audience->getIdentifier()}/members",
            ['count' => $count, 'offset' => $offset]
        );

        if (!$this->success()) {
            $this->logError($results);

            throw new NewsletterException("Failed to retrieve members.");
        }

        return $results['members'];
    }

    /**
     * Logs the error.
     *
     * @param array $data
     */
    public function logError(array $data = []): void
    {
        if (!empty($error = $this->getLastError())) {
            $this->logger->error(sprintf("[%s] %s", Constants::NAME, $error));
        }

        if (isset($data['errors'])) {
            foreach ($data['errors'] as $error) {
                if (isset($error['field']) && isset($error['message'])) {
                    $this->logger->error(sprintf("%s:%s", $error['field'], $error['message']));
                }
            }
        }
    }
}
