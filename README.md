# PSR-7 middleware to minify the response body

Simple PSR-7 Middleware that minifies the response body.

[![Build status][Master image]][Master]

## Installation

`composer require adriansuter/psr7-minify-middleware`

## Usage

In Slim 3:

```php
$app->add(new AdrianSuter\PSR7\Middleware\Minify());
```

## Testing

* Unit tests: ``$ vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/RendererTest``
