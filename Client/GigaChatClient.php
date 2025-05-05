<?php

namespace Maxyc\TelegramBot\Client;

use GuzzleHttp\Client;
use Maxyc\TelegramBot\ConfigProvider;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;

/**
 * Client for interacting with the GigaChat API.
 *
 * @psalm-immutable
 */
class GigaChatClient
{
    private readonly string $authKey;
    private readonly string $scope;
    private readonly Logger $logger;
    private readonly Client $httpClient;
    private readonly ConfigProvider $config;
    private string $accessToken;
    private int $tokenExpiresAt;

    /**
     * @param string $authKey GigaChat auth key
     * @param string $scope GigaChat scope
     * @param Logger $logger Logger instance
     * @param ConfigProvider $config Configuration provider
     */
    public function __construct(string $authKey, string $scope, Logger $logger, ConfigProvider $config)
    {
        $this->authKey = $authKey;
        $this->scope = $scope;
        $this->logger = $logger;
        $this->config = $config;
        $this->httpClient = new Client([
            'timeout' => $this->config->guzzleTimeout,
            'connect_timeout' => $this->config->guzzleConnectTimeout,
        ]);
        $this->refreshAccessToken();
    }

    /**
     * Sends a prompt to GigaChat and returns the response.
     *
     * @param string $prompt Prompt to send
     * @return string GigaChat response
     * @throws \Exception If the API request fails
     */
    public function ask(string $prompt): string
    {
        if ($this->tokenExpiresAt < time() + 60) {
            $this->refreshAccessToken();
        }

        try {
            $response = $this->httpClient->post($this->config->gigaChatApiUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'model' => $this->config->gigaChatModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['choices'][0]['message']['content'] ?? 'GigaChat response error';
        } catch (\Exception $e) {
            $this->logger->error('GigaChat API error', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to process GigaChat request');
        }
    }

    /**
     * Refreshes the GigaChat access token.
     *
     * @return void
     * @throws \Exception If token refresh fails
     */
    private function refreshAccessToken(): void
    {
        try {
            $response = $this->httpClient->post($this->config->gigaChatAuthUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                    'RqUID' => Uuid::uuid4()->toString(),
                    'Authorization' => 'Basic ' . base64_encode($this->authKey),
                ],
                'form_params' => [
                    'scope' => $this->scope,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $this->accessToken = $data['access_token'];
            $this->tokenExpiresAt = time() + ($data['expires_in'] ?? 1800);
        } catch (\Exception $e) {
            $this->logger->error('GigaChat token refresh error', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to obtain GigaChat access token');
        }
    }
}