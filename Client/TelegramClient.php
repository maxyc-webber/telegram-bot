<?php

namespace Maxyc\TelegramBot\Client;

use GuzzleHttp\Client;
use Maxyc\TelegramBot\ConfigProvider;
use Monolog\Logger;

/**
 * Client for interacting with the Telegram Bot API.
 *
 * @psalm-immutable
 */
class TelegramClient
{
    private readonly Client $httpClient;
    private readonly string $token;
    private readonly Logger $logger;

    /**
     * @param string $token Telegram bot token
     * @param Logger $logger Logger instance
     * @param ConfigProvider $config Configuration provider
     */
    public function __construct(string $token, Logger $logger, ConfigProvider $config)
    {
        $this->token = $token;
        $this->logger = $logger;
        $this->httpClient = new Client([
            'base_uri' => 'https://api.telegram.org/bot' . $token . '/',
            'timeout' => $config->guzzleTimeout,
            'connect_timeout' => $config->guzzleConnectTimeout,
        ]);
    }

    /**
     * Sends a message to a chat.
     *
     * @param array $params Message parameters (chat_id, text, etc.)
     * @return void
     * @throws \Exception If the API request fails
     * @psalm-param array{chat_id: int, text: string} $params
     */
    public function sendMessage(array $params): void
    {
        $this->request('sendMessage', $params);
    }

    /**
     * Deletes a message from a chat.
     *
     * @param array $params Deletion parameters (chat_id, message_id)
     * @return void
     * @throws \Exception If the API request fails
     * @psalm-param array{chat_id: int, message_id: int} $params
     */
    public function deleteMessage(array $params): void
    {
        $this->request('deleteMessage', $params);
    }

    /**
     * Bans a user from a chat.
     *
     * @param array $params Ban parameters (chat_id, user_id)
     * @return void
     * @throws \Exception If the API request fails
     * @psalm-param array{chat_id: int, user_id: int} $params
     */
    public function banChatMember(array $params): void
    {
        $this->request('banChatMember', $params);
    }

    /**
     * Retrieves the webhook update.
     *
     * @return array|null Webhook update data or null if invalid
     * @psalm-return array{message: array{message_id: int, chat: array{id: int}, from: array{id: int, username?: string, first_name?: string}, text?: string}}|null
     */
    public function getWebhookUpdate(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (!is_array($data) || !isset($data['message'])) {
            $this->logger->warning('Invalid webhook update', ['input' => $input]);
            return null;
        }
        return $data;
    }

    /**
     * Sends a request to the Telegram API.
     *
     * @param string $method API method
     * @param array $params Request parameters
     * @return void
     * @throws \Exception If the API request fails
     * @psalm-param array<string, mixed> $params
     */
    private function request(string $method, array $params): void
    {
        try {
            $response = $this->httpClient->post($method, [
                'json' => $params,
            ]);
            $data = json_decode($response->getBody(), true);
            if (!$data['ok']) {
                throw new \Exception($data['description'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $this->logger->error('Telegram API error', ['method' => $method, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}