<?php

namespace Maxyc\TelegramBot\Client;

use GuzzleHttp\Client;
use Maxyc\TelegramBot\ConfigProvider;
use Monolog\Logger;

/**
 * Client for interacting with the Akismet API to check for spam.
 *
 * @psalm-immutable
 */
class AkismetClient
{
    private readonly Client $httpClient;
    private readonly string $apiKey;
    private readonly string $blogUrl;
    private readonly Logger $logger;
    private readonly ConfigProvider $config;

    /**
     * @param string $apiKey Akismet API key
     * @param string $blogUrl Blog URL associated with the API key
     * @param Logger $logger Logger instance
     * @param ConfigProvider $config Configuration provider
     */
    public function __construct(string $apiKey, string $blogUrl, Logger $logger, ConfigProvider $config)
    {
        $this->apiKey = $apiKey;
        $this->blogUrl = $blogUrl;
        $this->logger = $logger;
        $this->config = $config;
        $this->httpClient = new Client([
            'timeout' => $this->config->guzzleTimeout,
            'connect_timeout' => $this->config->guzzleConnectTimeout,
        ]);
    }

    /**
     * Checks if the given content is spam.
     *
     * @param array $params Parameters for the spam check (e.g., comment_content, comment_author, user_ip)
     * @return bool True if the content is spam, false otherwise
     * @throws \Exception If the API request fails
     * @psalm-param array{comment_content: string, comment_author: string, user_ip: string} $params
     */
    public function commentCheck(array $params): bool
    {
        try {
            $response = $this->httpClient->post($this->config->akismetApiUrl, [
                'form_params' => array_merge([
                    'blog' => $this->blogUrl,
                    'user_ip' => $params['user_ip'] ?? '0.0.0.0',
                    'comment_content' => $params['comment_content'] ?? '',
                    'comment_author' => $params['comment_author'] ?? '',
                ], $params),
            ]);

            $result = (string) $response->getBody();
            if ($result === 'true') {
                return true;
            }
            if ($result === 'false') {
                return false;
            }
            $this->logger->error('Unexpected Akismet response', ['response' => $result]);
            throw new \Exception('Invalid Akismet response');
        } catch (\Exception $e) {
            $this->logger->error('Akismet API error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}