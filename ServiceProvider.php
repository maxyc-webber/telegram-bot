<?php

namespace Maxyc\TelegramBot;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Log\LoggerInterface;
use Maxyc\TelegramBot\Bot\TelegramBot;
use Maxyc\TelegramBot\Bot\MessageHandler;
use Maxyc\TelegramBot\Bot\TelegramClient;
use Maxyc\TelegramBot\Client\AkismetClient;
use Maxyc\TelegramBot\Client\GigaChatClient;
use Maxyc\TelegramBot\Checks\SpamStrategyFactory;

/**
 * Service provider for registering bot dependencies.
 *
 * @psalm-immutable
 */
class ServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, [
            'config',
            'logger',
            'telegram',
            'akismet',
            'gigaChat',
            'messageHandler',
            'promptBuilder',
            'spamStrategyFactory',
            TelegramBot::class,
        ], true);
    }

    /**
     * Registers services in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->getContainer()->add('config', function () {
            return new ConfigProvider($_ENV);
        });

        $this->getContainer()->add('logger', function () {
            // Note: Logger must be provided externally as PSR-3 LoggerInterface
            throw new \RuntimeException('LoggerInterface must be registered in the container');
        });

        $this->getContainer()->add('telegram', function () {
            $config = $this->getContainer()->get('config');
            return new TelegramClient(
                $config->telegramBotToken,
                $this->getContainer()->get('logger'),
                $config
            );
        });

        $this->getContainer()->add('akismet', function () {
            $config = $this->getContainer()->get('config');
            return new AkismetClient(
                $config->akismetApiKey,
                $config->akismetBlogUrl,
                $this->getContainer()->get('logger'),
                $config
            );
        });

        $this->getContainer()->add('gigaChat', function () {
            $config = $this->getContainer()->get('config');
            return new GigaChatClient(
                $config->gigaChatAuthKey,
                $config->gigaChatScope,
                $this->getContainer()->get('logger'),
                $config
            );
        });

        $this->getContainer()->add('promptBuilder', function () {
            return new PromptBuilder($this->getContainer()->get('config'));
        });

        $this->getContainer()->add('spamStrategyFactory', function () {
            return new SpamStrategyFactory();
        });

        $this->getContainer()->add('messageHandler', function () {
            $config = $this->getContainer()->get('config');
            $translations = include __DIR__ . "/translations/{$config->appLanguage}.php";
            return new MessageHandler(
                $this->getContainer()->get('logger'),
                $this->getContainer()->get('gigaChat'),
                $this->getContainer()->get('akismet'),
                $config,
                $this->getContainer()->get('promptBuilder'),
                $this->getContainer()->get('spamStrategyFactory'),
                $translations
            );
        });

        $this->getContainer()->add(TelegramBot::class, function () {
            $config = $this->getContainer()->get('config');
            return new TelegramBot(
                $this->getContainer()->get('telegram'),
                $this->getContainer()->get('logger'),
                $this->getContainer()->get('messageHandler'),
                $config
            );
        });
    }
}