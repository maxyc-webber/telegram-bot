<?php

namespace Maxyc\TelegramBot\Checks;

use Monolog\Logger;
use Maxyc\TelegramBot\Client\TelegramClient;
use Maxyc\TelegramBot\MessageContext;

/**
 * Strategy for sending a warning for spam messages.
 *
 * @psalm-immutable
 */
class WarnStrategy implements SpamHandlingStrategyInterface
{
    private readonly string $spamMessage;

    /**
     * @param string $spamMessage Warning message to send
     */
    public function __construct(string $spamMessage)
    {
        $this->spamMessage = $spamMessage;
    }

    /**
     * Handles a spam message by sending a warning.
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
        $telegram->sendMessage([
            'chat_id' => $context->chatId,
            'text' => $this->spamMessage,
        ]);
        $logger->info(str_replace('{strategy}', 'warn', $logContext['strategy'] ?? 'warn'), $logContext);
    }
}