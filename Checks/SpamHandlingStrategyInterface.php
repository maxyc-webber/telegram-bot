<?php

namespace Maxyc\TelegramBot\Checks;

use Monolog\Logger;
use Maxyc\TelegramBot\Client\TelegramClient;
use Maxyc\TelegramBot\MessageContext;

/**
 * Interface for spam handling strategies.
 *
 * @psalm-pure
 */
interface SpamHandlingStrategyInterface
{
    /**
     * Handles a spam message.
     *
     * @param TelegramClient $telegram Telegram API client
     * @param MessageContext $context Message context
     * @param Logger $logger Logger instance
     * @param array $logContext Logging context
     * @return void
     * @psalm-param array{chat_id: int, message_id: int, user_id: int, text: string, strategy: string} $logContext
     */
    public function handle(TelegramClient $telegram, MessageContext $context, Logger $logger, array $logContext): void;
}