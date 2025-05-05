<?php

namespace Maxyc\TelegramBot\Bot;

use Monolog\Logger;
use Maxyc\TelegramBot\Client\TelegramClient;
use Maxyc\TelegramBot\ConfigProvider;

/**
 * Main class for handling Telegram webhook requests.
 *
 * @psalm-immutable
 */
class TelegramBot
{
    private readonly TelegramClient $telegram;
    private readonly Logger $logger;
    private readonly MessageHandler $messageHandler;
    private readonly ?string $webhookSecret;

    /**
     * @param TelegramClient $telegram Telegram API client
     * @param Logger $logger Logger instance
     * @param MessageHandler $messageHandler Message handler
     * @param ConfigProvider $config Configuration provider
     */
    public function __construct(
        TelegramClient $telegram,
        Logger $logger,
        MessageHandler $messageHandler,
        ConfigProvider $config
    ) {
        $this->telegram = $telegram;
        $this->logger = $logger;
        $this->messageHandler = $messageHandler;
        $this->webhookSecret = $config->telegramWebhookSecret;
    }

    /**
     * Handles incoming Telegram webhook requests.
     *
     * @return void
     * @throws \RuntimeException If webhook signature is invalid
     */
    public function handleWebhook(): void
    {
        if ($this->webhookSecret) {
            $headers = getallheaders();
            $signature = $headers['X-Telegram-Bot-Api-Secret-Token'] ?? '';
            if ($signature !== $this->webhookSecret) {
                $this->logger->error('Invalid webhook signature', ['signature' => $signature]);
                throw new \RuntimeException('Invalid webhook signature');
            }
        }

        $update = $this->telegram->getWebhookUpdate();
        if ($update && isset($update['message'])) {
            $this->messageHandler->handle($this->telegram, $update['message']);
        }
    }
}