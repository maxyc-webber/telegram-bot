<?php

namespace Maxyc\TelegramBot\Checks;

use Monolog\Logger;
use Maxyc\TelegramBot\Client\TelegramClient;
use Maxyc\TelegramBot\MessageContext;

/**
 * Strategy for deleting spam messages and banning the user.
 *
 * @psalm-immutable
 */
class DeleteBanStrategy implements SpamHandlingStrategyInterface
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
     * Handles a spam message by sending a warning, deleting the message, and banning the user.
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
        $telegram->deleteMessage([
            'chat_id' => $context->chatId,
            'message_id' => $context->messageId,
        ]);
        $telegram->banChatMember([
            'chat_id' => $context->chatId,
            'user_id' => $context->userId,
        ]);
        $logger->info(str_replace('{strategy}', 'delete_ban', $logContext['strategy'] ?? 'delete_ban'), $logContext);
    }
}