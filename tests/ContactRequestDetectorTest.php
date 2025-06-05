<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Maxyc\TelegramBot\Checks\ContactRequestDetector;
use Maxyc\TelegramBot\ConfigProvider;

class ContactRequestDetectorTest extends TestCase
{
    private function createConfig(): ConfigProvider
    {
        $env = [
            'TELEGRAM_BOT_TOKEN' => 'token',
            'AKISMET_API_KEY' => 'key',
            'AKISMET_BLOG_URL' => 'https://example.com',
            'AKISMET_API_URL' => 'https://akismet.example.com',
            'GIGACHAT_AUTH_KEY' => 'auth',
            'GIGACHAT_SCOPE' => 'scope',
            'GIGACHAT_API_URL' => 'https://giga.api',
            'GIGACHAT_AUTH_URL' => 'https://giga.auth',
            'GIGACHAT_MODEL' => 'model',
            'GIGACHAT_PROMPT_TEMPLATE' => 'template',
            'DATA_PATH' => __DIR__ . '/../data',
            'LOG_PATH' => __DIR__,
            'APP_LANGUAGE' => 'en',
        ];

        return new ConfigProvider($env);
    }

    public function testIsContactRequestReturnsTrueIfPatternMatches(): void
    {
        $config = $this->createConfig();
        $detector = new ContactRequestDetector($config, ['/contact/i']);
        $this->assertTrue($detector->isContactRequest('please CONTACT me'));
    }

    public function testIsContactRequestReturnsFalseIfNoPatternMatches(): void
    {
        $config = $this->createConfig();
        $detector = new ContactRequestDetector($config, ['/contact/i']);
        $this->assertFalse($detector->isContactRequest('hello world'));
    }
}
