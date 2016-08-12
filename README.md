# MediaWiki SDK

[![Build Status](https://travis-ci.org/Imangazaliev/DiDOM.svg)](https://travis-ci.org/IT-Shura/MediaWiki-SDK)
[![Total Downloads](https://poser.pugx.org/it-shura/mediawiki-sdk/downloads)](https://packagist.org/packages/it-shura/mediawiki-sdk)
[![Latest Stable Version](https://poser.pugx.org/it-shura/mediawiki-sdk/v/stable)](https://packagist.org/packages/it-shura/mediawiki-sdk)
[![License](https://poser.pugx.org/it-shura/mediawiki-sdk/license)](https://packagist.org/packages/it-shura/mediawiki-sdk)

MediaWiki SDK - библиотека для работы с API MediaWiki.

## Содержание

- [Установка](#Установка)
- [Быстрый старт](#Быстрый-старт)
- [Авторизация](#Авторизация)
- [Выполнение запроса](#Выполнение-запроса)

## Установка

Для установки MediaWiki SDK выполните команду:

    composer require it-shura/mediawiki-sdk

## Быстрый старт

```php
use MediaWiki\Api\Api;
use MediaWiki\Storage\FileStore;
use MediaWiki\HttpClient\GuzzleHttpClient;

$url = 'http://ru.example.com/api.php';

$client = new GuzzleHttpClient();
$storage = new FileStore(__DIR__.'/storage/cache');

$api = new Api($url, $client, $storage);
```

## Авторизация

```php
$username = 'DummyUser';
$password = '123456';

try {
    $api->login($username, $password);
} catch (ApiException $exception) {
    echo sprintf('MediaWiki API Error: ', $exception->getMessage());

    exit;
}

// bool(true)
var_dump($api->isLoggedIn());

// выход
$api->logout();
```

## Выполнение запроса

```php
$parameters = [
    'action' => 'query',
    'list' => 'allpages',
];

$response = $this->request('POST', $parameters);

// или

$parameters = [
    'list' => 'allpages',
];

$response = $api->query($parameters);

var_dump($response);
```

### Параметры метода `request`

- **method** - HTTP-метод (POST/GET)
- **parameters** - параметры запроса (опционально)
- **headers** - заголовки запроса (опционально)
- **decode** - декодирует запрос, если передан параметр `true`  (только json, опционально)

### Параметры метода `query`

- **parameters** - параметры запроса
- **decode** - декодирует запрос, если передан параметр `true`  (только json, опционально)