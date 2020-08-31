<?php

namespace Ekyna\Component\Commerce\Bridge\SendInBlue;

use DateTime;
use Ekyna\Component\Commerce\Exception\NewsletterException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Exception;
use Psr\Log\LoggerInterface;
use SendinBlue\Client;

/**
 * Class Api
 * @package Ekyna\Component\Commerce\Bridge\SendInBlue
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Api
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client\Configuration
     */
    private $config;

    /**
     * @var Client\Api\ContactsApi
     */
    private $client;


    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param string          $apiKey
     */
    public function __construct(LoggerInterface $logger, string $apiKey)
    {
        $this->logger = $logger;
        $this->config = Client\Configuration::getDefaultConfiguration();
        $this->config->setApiKey('api-key', $apiKey);
    }

    /**
     * Returns the default folder id.
     *
     * @return int
     *
     * @throws NewsletterException
     */
    public function getDefaultFolderId(): int
    {
        try {
            $result = $this->getClient()->getFolders(10, 0);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            throw new NewsletterException("Failed to retrieve SendInBlue folders list.");
        }

        $folders = $result->getFolders();

        $folder = reset($folders);

        return (int)$folder['id'];
    }

    /**
     * Returns the lists.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array[]|null
     */
    public function getLists(int $limit = 10, int $offset = 0): ?array
    {
        try {
            $result = $this->getClient()->getLists($limit, $offset);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        return $result->getLists();
    }

    /**
     * Creates the list.
     *
     * @param string $name
     *
     * @return int|null
     */
    public function createList(string $name): ?int
    {
        $model = new Client\Model\CreateList();
        $model
            ->setFolderId($this->getDefaultFolderId())
            ->setName($name);

        try {
            $result = $this->getClient()->createList($model);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        return $result->getId();
    }

    /**
     * Updates the list.
     *
     * @param int    $id
     * @param string $name
     *
     * @return bool
     * @throws Exception
     */
    public function updateList(int $id, string $name): bool
    {
        $model = new Client\Model\UpdateList();
        $model->setName($name);

        try {
            $this->getClient()->updateList($id, $model);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Deletes the list.
     *
     * @param int $id
     *
     * @return bool
     * @throws Exception
     */
    public function deleteList(int $id): bool
    {
        try {
            $this->getClient()->deleteList($id);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Returns given audience's contacts.
     *
     * @param AudienceInterface $audience
     * @param int               $limit
     * @param int               $offset
     * @param DateTime|null     $since
     *
     * @return array|null
     */
    public function getListContacts(
        AudienceInterface $audience,
        int $limit = 10,
        int $offset = 0,
        DateTime $since = null
    ): ?array {
        try {
            $result = $this->getClient()->getContactsFromList($audience->getIdentifier(), $since, $limit, $offset);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        return $result->getContacts();
    }

    /**
     * Creates the contact.
     *
     * @param string $email
     * @param array  $attributes
     * @param array  $subscribedIds
     *
     * @return int|null
     * @throws Exception
     */
    public function createContact(string $email, array $attributes, array $subscribedIds = []): ?int
    {
        $model = new Client\Model\CreateContact();
        $model->setEmail($email);
        $model->setAttributes((object)$attributes);
        $model->setListIds($subscribedIds);

        try {
            $result = $this->getClient()->createContact($model);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        return $result->getId();
    }

    /**
     * Updates the contact.
     *
     * @param string $email
     * @param array  $attributes
     *
     * @return bool
     */
    public function updateContact(string $email, array $attributes): bool
    {
        $model = new Client\Model\UpdateContact();
        $model->setAttributes((object)$attributes);

        try {
            $this->getClient()->updateContact($email, $model);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Adds the contact (email) to the the given list (identifier).
     *
     * @param int $identifier
     * @param string $email
     *
     * @return bool
     */
    public function addContactToList(int $identifier, string $email): bool
    {
        $emails = new Client\Model\AddContactToList();
        $emails->setEmails([$email]);

        try {
            $this->getClient()->addContactToList($identifier, $emails);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Removes the contact (email) from the the given list (identifier).
     *
     * @param string $identifier
     * @param string $email
     *
     * @return bool
     */
    public function removeContactFromList(string $identifier, string $email): bool
    {
        $emails = new Client\Model\RemoveContactFromList();
        $emails->setEmails([$email]);

        try {
            $this->getClient()->removeContactFromList($identifier, $emails);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Deletes the contact.
     *
     * @param string $email
     *
     * @return bool
     */
    public function deleteContact(string $email): bool
    {
        try {
            $this->getClient()->deleteContact($email);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Lists the webhooks.
     *
     * @return array|null
     */
    public function listWebhooks(): ?array
    {
        $client = new Client\Api\WebhooksApi(null, $this->config);

        try {
            $result = $client->getWebhooks('marketing');
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        return $result->getWebhooks();
    }

    /**
     * Creates the webhook.
     *
     * @param array  $events
     * @param string $type
     * @param string $url
     *
     * @return bool
     */
    public function createWebhook(array $events, string $type, string $url): bool
    {
        $client = new Client\Api\WebhooksApi(null, $this->config);

        $model = new Client\Model\CreateWebhook();
        $model
            ->setEvents($events)
            ->setType($type)
            ->setUrl($url);

        try {
            $client->createWebhook($model);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    private function getClient(): Client\Api\ContactsApi
    {
        if ($this->client) {
            return $this->client;
        }

        return $this->client = new Client\Api\ContactsApi(null, $this->config);
    }
}
