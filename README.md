# PSR-7 middleware to minify the response body

[![Build status][Master image]][Master] [![Coverage Status](https://coveralls.io/repos/github/adriansuter/psr7-minify-middleware/badge.svg?branch=master)](https://coveralls.io/github/adriansuter/psr7-minify-middleware?branch=master)

Simple PSR-7 Middleware that minifies the response body. This middleware can be used to minify the html output.

By default, all `textarea` and `pre` sections would not be minified (ignored). This can be customized.

## Installation

`composer require adriansuter/psr7-minify-middleware`

## Usage

The constructor of this middleware has two parameters:
- A callback that returns a new object implementing the
  [`Psr\Http\Message\StreamInterface`](https://github.com/php-fig/http-message/blob/master/src/StreamInterface.php)
  in order to be able to minify the content.
- The html elements (tag names) that should be ignored. This parameter is
  optional and defaults to the array `['textarea', 'pre']`.

### In Slim 3:

```php
use AdrianSuter\PSR7\Middleware\Minify;
use Slim\Http\Body;

// Create the application $app
// [...]

$app->add(
    new Minify(
        function () {
            return new Body(fopen('php://temp', 'r+'));
        }
    )
);
```

In order to customize the html elements to be ignored, simply add a second
parameter to the constructor:

```php
$app->add(
    new Minify(
        function () {
            return new Body(fopen('php://temp', 'r+'));
        },
        ['script', 'textarea', 'pre', 'code']
    )
);
```

## Testing

* Unit tests: ``$ vendor/bin/phpunit``

[Master]: https://travis-ci.org/adriansuter/psr7-minify-middleware
[Master image]: https://travis-ci.org/adriansuter/psr7-minify-middleware.svg?branch=master
