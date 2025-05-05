<?php

namespace Maxyc\TelegramBot\Bot;

use Monolog\Logger;
use Maxyc\TelegramBot\Checks\SpamHandlingStrategyInterface;
use Maxyc\TelegramBot\Checks\SpamStrategyFactory;
use Maxyc\TelegramBot\Client\AkismetClient;
use Maxyc\TelegramBot\Client\GigaChatClient;
use Maxyc\TelegramBot\Checks\ContactRequestDetector;
use Maxyc\TelegramBot\ConfigProvider;
use Maxyc\TelegramBot\MessageContext;
use Maxyc\TelegramBot\PromptBuilder;

/**
 * Handles incoming Telegram messages, checking for spam and contact requests.
 *
 * @psalm-immutable
 */
class MessageHandler
{
    private readonly Logger $logger;
    private readonly GigaChatClient $gigaChat;
    private readonly ContactRequestDetector $detector;
    private readonly AkismetClient $akismet;
    private readonly array $translations;
    private readonly ConfigProvider $config;
    private readonly SpamHandlingStrategyInterface $spamStrategy;
    private readonly PromptBuilder $promptBuilder;

    /**
     * @param Logger $logger Logger instance
     * @param GigaChatClient $gigaChat GigaChat API client
     * @param AkismetClient $akismet Akismet API client
     * @param ConfigProvider $config Configuration provider
     * @param PromptBuilder $promptBuilder Prompt builder
     * @param SpamStrategyFactory $spamStrategyFactory Factory for spam strategies
     * @psalm-param array<string, string> $translations Translations array
     */
    public function __construct(
        Logger $logger,
        GigaChatClient $gigaChat,
        AkismetClient $akismet,
        ConfigProvider $config,
        PromptBuilder $promptBuilder,
        SpamStrategyFactory $spamStrategyFactory,
        array $translations = []
    ) {
        $this->logger = $logger;
        $this->gigaChat = $gigaChat;
        $this->detector = new ContactRequestDetector($config);
        $this->akismet = $akismet;
        $this->config = $config;
        $this->translations = $translations;
        $this->promptBuilder = $promptBuilder;
        $this->spamStrategy = $spamStrategyFactory->create($config->spamStrategy, $translations['spam_message'] ?? 'Spam detected');
    }

    /**
     * Processes a Telegram message, checking for spam or contact requests.
     *
     * @param TelegramClient $telegram Telegram API client
     * @param array $message Incoming message data
     * @return void
     * @psalm-param array{message_id: int, chat: array{id: int}, from: array{id: int, username?: string, first_name?: string}, text?: string} $message
     */
    public function handle(TelegramClient $telegram, array $message): void
    {
        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? 0;
        $messageId = $message['message_id'] ?? 0;
        $userId = $message['from']['id'] ?? 0;

        if (!$text || !$chatId || !$messageId || !$userId) {
            return;
        }

        $context = new MessageContext($chatId, $messageId, $userId, $text);

        try {
            // Check for spam
            if ($this->config->spamCheckEnabled) {
                $params = [
                    'comment_content' => $text,
                    'comment_author' => $message['from']['username'] ?? $message['from']['first_name'] ?? '',
                    'user_ip' => '0.0.0.0',
                ];
                if ($this->akismet->commentCheck($params)) {
                    $this->logger->warning($this->translations['log_spam_detected'] ?? 'Spam detected', $context->toArray());
                    $this->spamStrategy->handle($telegram, $context, $this->logger, $context->toArray() + ['strategy' => $this->config->spamStrategy]);
                    return;
                }
            }

            // Check for contact requests
            if ($this->config->contactCheckEnabled && $this->detector->isContactRequest($text)) {
                $specialists = $this->getSpecialists();
                $prompt = $this->promptBuilder->build($text, $specialists);
                $response = $this->gigaChat->ask($prompt);
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $response,
                ]);
                $this->logger->info($this->translations['log_contact_processed'] ?? 'Contact request processed', $context->toArray());
                return;
            }
        } catch (\Exception $e) {
            $this->logger->error(
                $this->translations['log_message_error'] ?? 'Message processing error',
                $context->toArray() + ['error' => $e->getMessage()]
            );
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $this->translations['error_message'] ?? 'An error occurred',
            ]);
        }
    }

    /**
     * Loads translations for the specified language.
     *
     * @param string $language Language code (e.g., 'en', 'ru')
     * @return array<string, string> Translations
     * @throws \RuntimeException If translation file is not found
     * @psalm-return array<string, string>
     */
    private function loadTranslations(string $language): array
    {
        $file = __DIR__ . "/../../translations/{$language}.php";
        if (!file_exists($file)) {
            throw new \RuntimeException("Translation file for language '$language' not found");
        }
        return include $file;
    }

    /**
     * Retrieves the list of specialists from the configured data file.
     *
     * @return array<array{phone: string, name: string, services: string, description: string}> List of specialists
     * @throws \Exception If the specialists file is not found
     * @psalm-return list<array{phone: string, name: string, services: string, description: string}>
     */
    private function getSpecialists(): array
    {
        $file = $this->config->dataPath . '/specialists.txt';
        if (!file_exists($file)) {
            $this->logger->error('Specialists file not found', ['file' => $file]);
            throw new \Exception('Specialists file not found');
        }

        $specialists = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match('/^(\+\d{10,12})\.\s*([^,]+),\s*([^,]+),\s*(.*)$/', trim($line), $matches)) {
                $specialists[] = [
                    'phone' => $matches[1],
                    'name' => trim($matches[2]),
                    'services' => trim($matches[3]),
                    'description' => trim($matches[4]),
                ];
            }
        }
        return $specialists;
    }
}