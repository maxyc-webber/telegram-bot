<?php

namespace Maxyc\TelegramBot\Checks;

use Maxyc\TelegramBot\ConfigProvider;

/**
 * Detects contact request messages using regex patterns.
 *
 * @psalm-immutable
 */
class ContactRequestDetector
{
    /** @var list<string> */
    private readonly array $patterns;

    /**
     * @param ConfigProvider $config Configuration provider
     * @param array|null $patterns Optional array of regex patterns
     * @psalm-param list<string>|null $patterns
     */
    public function __construct(ConfigProvider $config, ?array $patterns = null)
    {
        if ($patterns === null) {
            $file = $config->dataPath . '/contact_keywords.txt';
            $this->patterns = file_exists($file)
                ? array_map('trim', file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
                : [];
        } else {
            $this->patterns = $patterns;
        }
    }

    /**
     * Checks if the given text is a contact request.
     *
     * @param string $text Text to check
     * @return bool True if the text is a contact request, false otherwise
     */
    public function isContactRequest(string $text): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }
}