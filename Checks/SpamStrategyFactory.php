<?php

namespace Maxyc\TelegramBot\Checks;

use Maxyc\TelegramBot\Checks\WarnStrategy;
use Maxyc\TelegramBot\Checks\WarnDeleteStrategy;
use Maxyc\TelegramBot\Checks\SilentDeleteStrategy;
use Maxyc\TelegramBot\Checks\DeleteBanStrategy;

/**
 * Factory for creating spam handling strategies.
 *
 * @psalm-immutable
 */
class SpamStrategyFactory
{
    /**
     * Creates a spam handling strategy based on the given name.
     *
     * @param string $strategy Strategy name
     * @param string $spamMessage Warning message for strategies
     * @return SpamHandlingStrategyInterface
     */
    public function create(string $strategy, string $spamMessage): SpamHandlingStrategyInterface
    {
        return match ($strategy) {
            'warn' => new WarnStrategy($spamMessage),
            'warn_delete' => new WarnDeleteStrategy($spamMessage),
            'silent_delete' => new SilentDeleteStrategy(),
            'delete_ban' => new DeleteBanStrategy($spamMessage),
            default => new DeleteBanStrategy($spamMessage),
        };
    }
}