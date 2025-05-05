# Telegram-бот с проверкой спама и поиском контактов

Эта PHP-библиотека предоставляет Telegram-бота, который:
- Обнаруживает спам-сообщения с помощью прямых запросов к [Akismet API](https://akismet.com/) с настраиваемыми стратегиями обработки.
- Определяет запросы на поиск контактов специалистов по регулярным выражениям и использует [GigaChat](https://developers.sber.ru/docs/ru/gigachat/about) для поиска.
- Логирует действия с помощью [Monolog](https://github.com/Seldaek/monolog) с локализованными сообщениями.
- Управляет зависимостями через PSR-11 ([League\Container](https://container.thephpleague.com/)).
- Поддерживает локализацию через файлы `translations/{en|ru}.php`, выбираемые параметром `APP_LANGUAGE`.
- Проверяет подписи webhook для безопасности.
- Использует `ConfigProvider` для централизованной конфигурации, включая `GIGACHAT_PROMPT_TEMPLATE`.
- Реализует паттерн "Стратегия" для обработки спама с использованием DTO `MessageContext`.
- Использует собственный `TelegramClient` для взаимодействия с Telegram API.

**Автор**: Grechushnikov Maxim ([maxycws@gmail.com](mailto:maxycws@gmail.com), [Telegram: @maxyc](https://t.me/maxyc), [Веб-сайт: maxyc.ru](https://maxyc.ru))

**Репозиторий**: [https://github.com/maxyc-webber/telegram-bot](https://github.com/maxyc-webber/telegram-bot)

## Возможности
- Обнаружение спама с помощью стратегий (`warn`, `warn_delete`, `silent_delete`, `delete_ban`) с использованием `SpamHandlingStrategyInterface` и `MessageContext`.
- Поиск контактов с настраиваемым промптом GigaChat через `GIGACHAT_PROMPT_TEMPLATE`.
- Настраиваемые проверки сообщений через `MessageCheckerInterface`.
- Конфигурируемые пути, настройки API и язык через `ConfigProvider`.
- Локализованные сообщения и логи через `translations/{language}.php`.
- Детальное логирование и проверка подписи webhook.

## Требования
- PHP >= 8.1
- Composer
- Токен Telegram-бота (от @BotFather)
- API-ключ Akismet (от [akismet.com](https://akismet.com/))
- Ключ и область действия GigaChat (от [Sberbank](https://developers.sber.ru/))
- Права администратора в групповых чатах Telegram (для удаления сообщений и бана)

## Установка
1. Установите библиотеку через Composer:
   ```bash
   composer require maxycws/telegram-bot
   ```
2. Скопируйте `.env.example` в `.env` и заполните учетные данные:
   ```
   # Обязательные настройки
   TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
   AKISMET_API_KEY=your_akismet_api_key_here
   AKISMET_BLOG_URL=https://your-blog-url.com
   AKISMET_API_URL=https://your_key.rest.akismet.com/1.1/comment-check
   GIGACHAT_AUTH_KEY=your_gigachat_auth_key_here
   GIGACHAT_SCOPE=your_gigachat_scope_here
   GIGACHAT_API_URL=https://gigachat.devices.sberbank.ru/api/v1
   GIGACHAT_AUTH_URL=https://ngw.devices.sberbank.ru:9443/api/v2/oauth
   GIGACHAT_MODEL=GigaChat
   GIGACHAT_PROMPT_TEMPLATE=Пользовательский запрос: {userMessage}\n\nТаблица специалистов:\n{specialistsText}\n\nНайдите специалистов, которые могут помочь с запросом пользователя, и предоставьте их имена и телефоны.
   DATA_PATH=data/
   LOG_PATH=logs/bot.log
   APP_LANGUAGE=ru

   # Необязательные настройки
   TELEGRAM_WEBHOOK_SECRET=your_webhook_secret_here
   SPAM_CHECK_ENABLED=true
   CONTACT_CHECK_ENABLED=true
   SPAM_STRATEGY=delete_ban
   GUZZLE_TIMEOUT=5
   GUZZLE_CONNECT_TIMEOUT=2
   ```
3. Создайте директории для `DATA_PATH` и `LOG_PATH` и добавьте:
   - `data/specialists.txt`: Список специалистов.
   - `data/contact_keywords.txt`: Регулярные выражения.
   - `translations/{en|ru}.php`: Файлы переводов.
4. Настройте webhook для Telegram-бота:
   ```bash
   curl -F "url=https://your-domain.com/webhook" -F "secret_token=your_webhook_secret_here" https://api.telegram.org/bot<your_bot_token>/setWebhook
   ```

## Конфигурация
Файл `.env` содержит настройки, разделенные на **обязательные** и **необязательные**. Ниже приведено подробное описание каждой настройки:

### Обязательные настройки
- **`TELEGRAM_BOT_TOKEN`**:
  - **Назначение**: Токен, полученный от @BotFather для аутентификации бота в Telegram API.
  - **Значение**: Строка, например, `123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`.
  - **Пример**: `TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`
- **`AKISMET_API_KEY`**:
  - **Назначение**: Ключ API от [akismet.com](https://akismet.com/) для проверки спама.
  - **Значение**: Строка, например, `1234567890ab`.
  - **Пример**: `AKISMET_API_KEY=1234567890ab`
- **`AKISMET_BLOG_URL`**:
  - **Назначение**: URL сайта, связанного с аккаунтом Akismet.
  - **Значение**: Валидный URL, например, `https://example.com`.
  - **Пример**: `AKISMET_BLOG_URL=https://example.com`
- **`AKISMET_API_URL`**:
  - **Назначение**: Конечная точка API Akismet для проверки спама, включающая ключ API.
  - **Значение**: URL с заполнителем ключа, например, `https://your_key.rest.akismet.com/1.1/comment-check`.
  - **Пример**: `AKISMET_API_URL=https://1234567890ab.rest.akismet.com/1.1/comment-check`
- **`GIGACHAT_AUTH_KEY`**:
  - **Назначение**: Ключ аутентификации для API GigaChat от Сбербанка.
  - **Значение**: Строка, например, `e3650d8e-d96b-45db-bc7d-6a15357b9904`.
  - **Пример**: `GIGACHAT_AUTH_KEY=e3650d8e-d96b-45db-bc7d-6a15357b9904`
- **`GIGACHAT_SCOPE`**:
  - **Назначение**: Область действия для аутентификации API GigaChat.
  - **Значение**: Строка, например, `GIGACHAT_API_PERS`.
  - **Пример**: `GIGACHAT_SCOPE=GIGACHAT_API_PERS`
- **`GIGACHAT_API_URL`**:
  - **Назначение**: Базовый URL для запросов к API GigaChat.
  - **Значение**: URL, например, `https://gigachat.devices.sberbank.ru/api/v1`.
  - **Пример**: `GIGACHAT_API_URL=https://gigachat.devices.sberbank.ru/api/v1`
- **`GIGACHAT_AUTH_URL`**:
  - **Назначение**: URL для аутентификации GigaChat (OAuth).
  - **Значение**: URL, например, `https://ngw.devices.sberbank.ru:9443/api/v2/oauth`.
  - **Пример**: `GIGACHAT_AUTH_URL=https://ngw.devices.sberbank.ru:9443/api/v2/oauth`
- **`GIGACHAT_MODEL`**:
  - **Назначение**: Модель GigaChat для обработки запросов.
  - **Значение**: Строка, например, `GigaChat`.
  - **Пример**: `GIGACHAT_MODEL=GigaChat`
- **`GIGACHAT_PROMPT_TEMPLATE`**:
  - **Назначение**: Шаблон для создания промптов, отправляемых в GigaChat. Использует заполнители `{userMessage}` и `{specialistsText}`.
  - **Значение**: Строка с заполнителями, например, `Пользовательский запрос: {userMessage}\n\nТаблица специалистов:\n{specialistsText}\n\nНайдите специалистов, которые могут помочь с запросом пользователя, и предоставьте их имена и телефоны.`.
  - **Пример**: `GIGACHAT_PROMPT_TEMPLATE=Пользовательский запрос: {userMessage}\n\nТаблица специалистов:\n{specialistsText}\n\nНайдите специалистов, которые могут помочь с запросом пользователя, и предоставьте их имена и телефоны.`
- **`DATA_PATH`**:
  - **Назначение**: Путь к директории для файлов данных (`specialists.txt`, `contact_keywords.txt`).
  - **Значение**: Путь к директории, например, `data/`.
  - **Пример**: `DATA_PATH=data/`
- **`LOG_PATH`**:
  - **Назначение**: Путь к файлу логов.
  - **Значение**: Путь к файлу, например, `logs/bot.log`.
  - **Пример**: `LOG_PATH=logs/bot.log`
- **`APP_LANGUAGE`**:
  - **Назначение**: Язык для сообщений и логов, соответствующий файлу переводов в `translations/`.
  - **Значение**: Код языка, например, `en` или `ru`.
  - **Пример**: `APP_LANGUAGE=ru`

### Необязательные настройки
- **`TELEGRAM_WEBHOOK_SECRET`**:
  - **Назначение**: Секретный токен для проверки webhook-запросов Telegram.
  - **Значение**: Строка или пустое значение для отключения проверки.
  - **Пример**: `TELEGRAM_WEBHOOK_SECRET=your_secret_token`
- **`SPAM_CHECK_ENABLED`**:
  - **Назначение**: Включает или отключает проверку спама с помощью Akismet.
  - **Значение**: `true` или `false`.
  - **Пример**: `SPAM_CHECK_ENABLED=true`
- **`CONTACT_CHECK_ENABLED`**:
  - **Назначение**: Включает или отключает обнаружение запросов на контакты и обработку через GigaChat.
  - **Значение**: `true` или `false`.
  - **Пример**: `CONTACT_CHECK_ENABLED=true`
- **`SPAM_STRATEGY`**:
  - **Назначение**: Указывает стратегию обработки спам-сообщений.
  - **Значение**: Одна из: `warn`, `warn_delete`, `silent_delete`, `delete_ban`.
  - **Пример**: `SPAM_STRATEGY=delete_ban`
- **`GUZZLE_TIMEOUT`**:
  - **Назначение**: Тайм-аут для HTTP-запросов в секундах.
  - **Значение**: Положительное число, например, `5`.
  - **Пример**: `GUZZLE_TIMEOUT=5`
- **`GUZZLE_CONNECT_TIMEOUT`**:
  - **Назначение**: Тайм-аут подключения для HTTP-запросов в секундах.
  - **Значение**: Положительное число, например, `2`.
  - **Пример**: `GUZZLE_CONNECT_TIMEOUT=2`

## Использование
### Автономное использование
Создайте скрипт для обработки webhook (например, `webhook.php`):
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

### Пользовательские проверки и стратегии
Реализуйте пользовательскую стратегию обработки спама:
```php
namespace Maxyc\TelegramBot\Checks;

use Monolog\Logger;
use Maxyc\TelegramBot\Bot\TelegramClient;
use Maxyc\TelegramBot\MessageContext;

class CustomSpamStrategy implements SpamHandlingStrategyInterface
{
    public function handle(TelegramClient $telegram, MessageContext $context, Logger $logger, array $logContext): void
    {
        $telegram->sendMessage([
            'chat_id' => $context->chatId,
            'text' => 'Обнаружен пользовательский спам!',
        ]);
        $logger->info('Обработан пользовательский спам', $logContext);
    }
}
```

## Тестирование
Запустите юнит-тесты с помощью PHPUnit:
```bash
vendor/bin/phpunit tests
```

## Вклад в проект
Следуйте [CONTRIBUTING.md](CONTRIBUTING.md) и [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## История изменений
См. [CHANGELOG.md](CHANGELOG.md).

## Лицензия
[MIT License](LICENSE)

## Поддержка
- [GitHub Issues](https://github.com/maxyc-webber/telegram-bot/issues)
- Email: [maxycws@gmail.com](mailto:maxycws@gmail.com)
- Telegram: [@maxyc](https://t.me/maxyc)
- Веб-сайт: [maxyc.ru](https://maxyc.ru)