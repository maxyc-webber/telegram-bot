<?php

namespace Maxyc\TelegramBot\Checks;

use Monolog\Logger;
use Maxyc\TelegramBot\Client\TelegramClient;
use Maxyc\TelegramBot\MessageContext;

/**
 * Strategy for silently deleting spam messages.
 *
 * @psalm-immutable
 */
class SilentDeleteStrategy implements SpamHandlingStrategyInterface
{
    /**
     * Handles a spam message by silently deleting it.
     *
     * @param TelegramClient $telegram Telegram API client
     * @param MessageContext $context Message context
     * @param Logger $logger Logger instance
     * @param array $logContext Logging context
     * @return void
     * @psalm-param array{chat_id: int, message_id: int, user_id: int, text: string, strategy: string} $logContext
     */
    public function handle(TelegramClient $telegram, MessageContext $context, Logger $logger, array $logContext): void
    {
        $telegram->deleteMessage([
            'chat_id' => $context->chatId,
            'message_id' => $context->messageId,
        ]);
        $logger->info(str_replace('{strategy}', 'silent_delete', $logContext['strategy'] ?? 'silent_delete'), $logContext);
    }
}