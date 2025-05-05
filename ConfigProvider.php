<?php

namespace Maxyc\TelegramBot;

/**
 * Provides configuration settings for the application.
 *
 * @psalm-immutable
 */
class ConfigProvider
{
    public readonly string $telegramBotToken;
    public readonly ?string $telegramWebhookSecret;
    public readonly string $akismetApiKey;
    public readonly string $akismetBlogUrl;
    public readonly string $akismetApiUrl;
    public readonly string $gigaChatAuthKey;
    public readonly string $gigaChatScope;
    public readonly string $gigaChatApiUrl;
    public readonly string $gigaChatAuthUrl;
    public readonly string $gigaChatModel;
    public readonly string $gigaChatPromptTemplate;
    public readonly bool $spamCheckEnabled;
    public readonly bool $contactCheckEnabled;
    public readonly string $spamStrategy;
    public readonly string $dataPath;
    public readonly string $logPath;
    public readonly float $guzzleTimeout;
    public readonly float $guzzleConnectTimeout;
    public readonly string $appLanguage;

    /**
     * @param array $env Environment variables
     * @throws \RuntimeException If required variables are missing
     * @psalm-param array<string, string|bool> $env
     */
    public function __construct(array $env)
    {
        $required = [
            'TELEGRAM_BOT_TOKEN',
            'AKISMET_API_KEY',
            'AKISMET_BLOG_URL',
            'AKISMET_API_URL',
            'GIGACHAT_AUTH_KEY',
            'GIGACHAT_SCOPE',
            'GIGACHAT_API_URL',
            'GIGACHAT_AUTH_URL',
            'GIGACHAT_MODEL',
            'GIGACHAT_PROMPT_TEMPLATE',
            'DATA_PATH',
            'LOG_PATH',
            'APP_LANGUAGE',
        ];
        foreach ($required as $key) {
            if (empty($env[$key])) {
                throw new \RuntimeException("Missing required environment variable: $key");
            }
        }

        $this->telegramBotToken = $env['TELEGRAM_BOT_TOKEN'];
        $this->telegramWebhookSecret = $env['TELEGRAM_WEBHOOK_SECRET'] ?? null;
        $this->akismetApiKey = $env['AKISMET_API_KEY'];
        $this->akismetBlogUrl = $env['AKISMET_BLOG_URL'];
        $this->akismetApiUrl = $env['AKISMET_API_URL'];
        $this->gigaChatAuthKey = $env['GIGACHAT_AUTH_KEY'];
        $this->gigaChatScope = $env['GIGACHAT_SCOPE'];
        $this->gigaChatApiUrl = $env['GIGACHAT_API_URL'];
        $this->gigaChatAuthUrl = $env['GIGACHAT_AUTH_URL'];
        $this->gigaChatModel = $env['GIGACHAT_MODEL'];
        $this->gigaChatPromptTemplate = $env['GIGACHAT_PROMPT_TEMPLATE'];
        $this->spamCheckEnabled = filter_var($env['SPAM_CHECK_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->contactCheckEnabled = filter_var($env['CONTACT_CHECK_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->spamStrategy = $env['SPAM_STRATEGY'] ?? 'delete_ban';
        $this->dataPath = rtrim($env['DATA_PATH'], '/');
        $this->logPath = $env['LOG_PATH'];
        $this->guzzleTimeout = (float) ($env['GUZZLE_TIMEOUT'] ?? 5);
        $this->guzzleConnectTimeout = (float) ($env['GUZZLE_CONNECT_TIMEOUT'] ?? 2);
        $this->appLanguage = $env['APP_LANGUAGE'];
    }
}