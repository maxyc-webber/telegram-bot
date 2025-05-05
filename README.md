# Telegram Bot with Spam Detection and Contact Search

This PHP library provides a Telegram bot that:
- Detects spam messages using direct [Akismet API](https://akismet.com/) requests with configurable handling strategies.
- Identifies contact search requests using regex patterns and queries [GigaChat](https://developers.sber.ru/docs/ru/gigachat/about) for specialist contacts.
- Logs actions with [Monolog](https://github.com/Seldaek/monolog) using localized messages.
- Manages dependencies with PSR-11 ([League\Container](https://container.thephpleague.com/)).
- Supports localization via `translations/{en|ru}.php`, selected by `APP_LANGUAGE`.
- Verifies webhook signatures for security.
- Uses `ConfigProvider` for centralized configuration, including `GIGACHAT_PROMPT_TEMPLATE`.
- Implements the Strategy pattern for spam handling with `MessageContext` DTO.
- Uses a custom `TelegramClient` for Telegram API interactions.

**Author**: Grechushnikov Maxim ([maxycws@gmail.com](mailto:maxycws@gmail.com), [Telegram: @maxyc](https://t.me/maxyc), [Website: maxyc.ru](https://maxyc.ru))

**Repository**: [https://github.com/maxyc-webber/telegram-bot](https://github.com/maxyc-webber/telegram-bot)

## Features
- Spam detection with strategies (warn, warn_delete, silent_delete, delete_ban) using `SpamHandlingStrategyInterface` and `MessageContext`.
- Contact search with customizable GigaChat prompt via `GIGACHAT_PROMPT_TEMPLATE`.
- Customizable message checkers via `MessageCheckerInterface`.
- Configurable paths, API settings, and language via `ConfigProvider`.
- Localized messages and logs via `translations/{language}.php`.
- Detailed logging and webhook signature verification.

## Requirements
- PHP >= 8.1
- Composer
- Telegram bot token (from @BotFather)
- Akismet API key (from [akismet.com](https://akismet.com/))
- GigaChat auth key and scope (from [Sberbank](https://developers.sber.ru/))
- Admin rights in Telegram group chats (for banning and deleting messages)

## Installation
1. Install the library via Composer:
   ```bash
   composer require maxycws/telegram-bot
   ```
2. Copy `.env.example` to `.env` and fill in your credentials:
   ```
   # Required Settings
   TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
   AKISMET_API_KEY=your_akismet_api_key_here
   AKISMET_BLOG_URL=https://your-blog-url.com
   AKISMET_API_URL=https://your_key.rest.akismet.com/1.1/comment-check
   GIGACHAT_AUTH_KEY=your_gigachat_auth_key_here
   GIGACHAT_SCOPE=your_gigachat_scope_here
   GIGACHAT_API_URL=https://gigachat.devices.sberbank.ru/api/v1
   GIGACHAT_AUTH_URL=https://ngw.devices.sberbank.ru:9443/api/v2/oauth
   GIGACHAT_MODEL=GigaChat
   GIGACHAT_PROMPT_TEMPLATE=User request: {userMessage}\n\nSpecialists:\n{specialistsText}\n\nFind specialists who can help.
   DATA_PATH=data/
   LOG_PATH=logs/bot.log
   APP_LANGUAGE=ru

   # Optional Settings
   TELEGRAM_WEBHOOK_SECRET=your_webhook_secret_here
   SPAM_CHECK_ENABLED=true
   CONTACT_CHECK_ENABLED=true
   SPAM_STRATEGY=delete_ban
   GUZZLE_TIMEOUT=5
   GUZZLE_CONNECT_TIMEOUT=2
   ```
3. Create directories for `DATA_PATH` and `LOG_PATH` and add:
   - `data/specialists.txt`: List of specialists.
   - `data/contact_keywords.txt`: Regex patterns.
   - `translations/{en|ru}.php`: Translation files.
4. Set up a Telegram webhook:
   ```bash
   curl -F "url=https://your-domain.com/webhook" -F "secret_token=your_webhook_secret_here" https://api.telegram.org/bot<your_bot_token>/setWebhook
   ```

## Configuration
The `.env` file contains configuration settings, divided into **Required** and **Optional** sections. Below is a detailed description of each setting:

### Required Settings
- **`TELEGRAM_BOT_TOKEN`**:
  - **Purpose**: The token obtained from @BotFather to authenticate the bot with the Telegram API.
  - **Value**: A string, e.g., `123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`.
  - **Example**: `TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`
- **`AKISMET_API_KEY`**:
  - **Purpose**: The API key from [akismet.com](https://akismet.com/) for spam detection.
  - **Value**: A string, e.g., `1234567890ab`.
  - **Example**: `AKISMET_API_KEY=1234567890ab`
- **`AKISMET_BLOG_URL`**:
  - **Purpose**: The URL of the site associated with the Akismet account.
  - **Value**: A valid URL, e.g., `https://example.com`.
  - **Example**: `AKISMET_BLOG_URL=https://example.com`
- **`AKISMET_API_URL`**:
  - **Purpose**: The Akismet API endpoint for spam checking, including the API key.
  - **Value**: A URL with the key placeholder, e.g., `https://your_key.rest.akismet.com/1.1/comment-check`.
  - **Example**: `AKISMET_API_URL=https://1234567890ab.rest.akismet.com/1.1/comment-check`
- **`GIGACHAT_AUTH_KEY`**:
  - **Purpose**: The authentication key for the GigaChat API from Sberbank.
  - **Value**: A string, e.g., `e3650d8e-d96b-45db-bc7d-6a15357b9904`.
  - **Example**: `GIGACHAT_AUTH_KEY=e3650d8e-d96b-45db-bc7d-6a15357b9904`
- **`GIGACHAT_SCOPE`**:
  - **Purpose**: The scope for GigaChat API authentication.
  - **Value**: A string, e.g., `GIGACHAT_API_PERS`.
  - **Example**: `GIGACHAT_SCOPE=GIGACHAT_API_PERS`
- **`GIGACHAT_API_URL`**:
  - **Purpose**: The base URL for GigaChat API requests.
  - **Value**: A URL, e.g., `https://gigachat.devices.sberbank.ru/api/v1`.
  - **Example**: `GIGACHAT_API_URL=https://gigachat.devices.sberbank.ru/api/v1`
- **`GIGACHAT_AUTH_URL`**:
  - **Purpose**: The URL for GigaChat authentication (OAuth).
  - **Value**: A URL, e.g., `https://ngw.devices.sberbank.ru:9443/api/v2/oauth`.
  - **Example**: `GIGACHAT_AUTH_URL=https://ngw.devices.sberbank.ru:9443/api/v2/oauth`
- **`GIGACHAT_MODEL`**:
  - **Purpose**: The GigaChat model to use for processing requests.
  - **Value**: A string, e.g., `GigaChat`.
  - **Example**: `GIGACHAT_MODEL=GigaChat`
- **`GIGACHAT_PROMPT_TEMPLATE`**:
  - **Purpose**: The template for constructing prompts sent to GigaChat. Uses `{userMessage}` and `{specialistsText}` placeholders.
  - **Value**: A string with placeholders, e.g., `User request: {userMessage}\n\nSpecialists:\n{specialistsText}\n\nFind specialists who can help.`.
  - **Example**: `GIGACHAT_PROMPT_TEMPLATE=User request: {userMessage}\n\nSpecialists:\n{specialistsText}\n\nFind specialists who can help.`
- **`DATA_PATH`**:
  - **Purpose**: The directory path for data files (`specialists.txt`, `contact_keywords.txt`).
  - **Value**: A directory path, e.g., `data/`.
  - **Example**: `DATA_PATH=data/`
- **`LOG_PATH`**:
  - **Purpose**: The file path for the log file.
  - **Value**: A file path, e.g., `logs/bot.log`.
  - **Example**: `LOG_PATH=logs/bot.log`
- **`APP_LANGUAGE`**:
  - **Purpose**: The language for messages and logs, corresponding to a translation file in `translations/`.
  - **Value**: A language code, e.g., `en` or `ru`.
  - **Example**: `APP_LANGUAGE=ru`

### Optional Settings
- **`TELEGRAM_WEBHOOK_SECRET`**:
  - **Purpose**: A secret token for verifying Telegram webhook requests.
  - **Value**: A string or empty to disable verification.
  - **Example**: `TELEGRAM_WEBHOOK_SECRET=your_secret_token`
- **`SPAM_CHECK_ENABLED`**:
  - **Purpose**: Enables or disables spam checking with Akismet.
  - **Value**: `true` or `false`.
  - **Example**: `SPAM_CHECK_ENABLED=true`
- **`CONTACT_CHECK_ENABLED`**:
  - **Purpose**: Enables or disables contact request detection and GigaChat processing.
  - **Value**: `true` or `false`.
  - **Example**: `CONTACT_CHECK_ENABLED=true`
- **`SPAM_STRATEGY`**:
  - **Purpose**: Specifies the strategy for handling spam messages.
  - **Value**: One of `warn`, `warn_delete`, `silent_delete`, `delete_ban`.
  - **Example**: `SPAM_STRATEGY=delete_ban`
- **`GUZZLE_TIMEOUT`**:
  - **Purpose**: Timeout for HTTP requests in seconds.
  - **Value**: A positive number, e.g., `5`.
  - **Example**: `GUZZLE_TIMEOUT=5`
- **`GUZZLE_CONNECT_TIMEOUT`**:
  - **Purpose**: Connect timeout for HTTP requests in seconds.
  - **Value**: A positive number, e.g., `2`.
  - **Example**: `GUZZLE_CONNECT_TIMEOUT=2`

## Usage
### Standalone Usage
```php
<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Maxyc\TelegramBot\Bot\TelegramBot;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = require __DIR__ . '/container.php';
$bot = $container->get(TelegramBot::class);
$bot->handleWebhook();
```

### Custom Checkers and Strategies
Implement custom spam strategies:
```php
namespace Maxyc\TelegramBot\Checks;

use Monolog\Logger;
use Telegram\Bot\Api;
use Maxyc\TelegramBot\MessageContext;

class CustomSpamStrategy implements SpamHandlingStrategyInterface
{
    public function handle(Api $telegram, MessageContext $context, Logger $logger, array $logContext): void
    {
        $telegram->sendMessage([
            'chat_id' => $context->chatId,
            'text' => 'Custom spam handling!',
        ]);
        $logger->info('Custom spam handling', $logContext);
    }
}
```

## Testing
Run unit tests with PHPUnit:
```bash
vendor/bin/phpunit tests
```

## Contributing
Follow [CONTRIBUTING.md](CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## Changelog
See [CHANGELOG.md](CHANGELOG.md).

## License
[MIT License](LICENSE)

## Support
- [GitHub Issues](https://github.com/maxyc-webber/telegram-bot/issues)
- Email: [maxycws@gmail.com](mailto:maxycws@gmail.com)
- Telegram: [@maxyc](https://t.me/maxyc)
- Website: [maxyc.ru](https://maxyc.ru)