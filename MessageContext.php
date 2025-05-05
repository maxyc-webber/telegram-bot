<?php

namespace Maxyc\TelegramBot;

/**
 * Data Transfer Object for message handling context.
 *
 * @psalm-immutable
 */
class MessageContext
{
    public readonly int $chatId;
    public readonly int $messageId;
    public readonly int $userId;
    public readonly string $text;

    /**
     * @param int $chatId Chat ID
     * @param int $messageId Message ID
     * @param int $userId User ID
     * @param string $text Message text
     */
    public function __construct(int $chatId, int $messageId, int $userId, string $text)
    {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->userId = $userId;
        $this->text = $text;
    }

    /**
     * Converts the context to an array for logging.
     *
     * @return array<string, string|int> Context data
     * @psalm-return array{chat_id: int, message_id: int, user_id: int, text: string}
     */
    public function toArray(): array
    {
        return [
            'chat_id' => $this->chatId,
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'text' => $this->text,
        ];
    }
}