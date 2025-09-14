# Setka CMS

**Setka CMS** — модульное ядро CMS на базе [Yii2](https://www.yiiframework.com/).  
Разработано для создания гибких сайтов и приложений с поддержкой **элементов, коллекций, полей, плагинов, REST/GraphQL API** и удобной админкой.

## 🚀 Возможности

- 📦 Ядро как Composer-библиотека (`setkacms/cms`).
- 🔗 Скелет-проект для быстрого старта (`setkacms/skeleton`).
- 🧩 Расширяемость через плагины:
  - регистрация новых типов полей,
  - добавление роутов REST,
  - расширение GraphQL-схем,
  - подключение собственных миграций.
- 🗄️ ORM-стиль работы с контентом: элементы, коллекции, поля.
- 🔒 Встроенные механизмы безопасности (хеширование паролей, RBAC).
- 🧪 Поддержка unit и integration тестов.
- ⚙️ Инфраструктура для CI/CD и анализа кода.

---

## 📦 Установка

Установите пакет через Composer:

```bash
composer require setkacms/cms
```

Для нового проекта рекомендуется использовать **skeleton**:

```bash
composer create-project setkacms/skeleton my-project
```

---

## ⚡ Быстрый старт

Пример запуска консольного приложения:

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Setka\Cms\Application('console', __DIR__);
$app->run();
```

Пример плагина:

```php
namespace Acme\SetkaSeo;

use Setka\Cms\Contracts\Plugins\PluginInterface;
use Setka\Cms\Plugins\Context\PluginContext;

final class Plugin implements PluginInterface
{
    public function register(PluginContext $ctx): void
    {
        $ctx->addRoute('GET', '/seo/check', fn() => ['status' => 'ok']);
        $ctx->addMigrationPath(__DIR__ . '/../migrations');
    }
}
```

`composer.json` плагина:

```json
{
  "name": "acme/setka-plugin-seo",
  "require": {
    "setkacms/cms": "^1.0"
  },
  "autoload": {
    "psr-4": {
      "Acme\\SetkaSeo\\": "src/"
    }
  },
  "extra": {
    "setka": {
      "plugin-class": "Acme\\SetkaSeo\\Plugin"
    }
  }
}
```

---

## 🛠️ Команды CLI

Через бинарь `bin/setka` (или `vendor/bin/setka`):

```bash
# Установка ядра и запуск миграций
setka install

# Управление миграциями
setka migrate

# Создание администратора
setka user/create --email=admin@example.com --password=secret
```

---

## 🧪 Тестирование

```bash
composer test
```

Запускаются unit и integration тесты (`phpunit`).

---

## 🔍 Анализ кода

```bash
# Проверка code style
composer cs -- --dry-run

# Статический анализ
composer stan

# Автоматический рефакторинг
composer rector -- --dry-run
```

---

## 📚 Документация

- [Архитектура и структура пакета](docs/architecture.md) *(в разработке)*  
- [Руководство по созданию плагинов](docs/plugins.md) *(в разработке)*  
- [API Reference](docs/api.md) *(в разработке)*  

---

## 🤝 Вклад в проект

Pull Requests приветствуются!  
Пожалуйста, перед отправкой запускайте:

```bash
composer cs
composer stan
composer test
```

---

## 📄 Лицензия

© Setka CMS. Все права защищены.  
Подробности см. в файле [LICENSE](LICENSE).
