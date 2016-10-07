# PSR-7 middleware to minify the response body

Simple PSR-7 Middleware that minifies the response body.

[![Build status][Master image]][Master]

## Installation

`composer require adriansuter/psr7-minify-middleware`

## Usage

The constructor of this middleware needs a callback that returns a new object
implementing the `([Psr\Http\Message\StreamInterface](https://github.com/php-fig/http-message/blob/master/src/StreamInterface.php))`
in order to be able to minify the content.

In Slim 3:

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

## Testing

* Unit tests: ``$ vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/RendererTest``

[Master]: https://travis-ci.org/adriansuter/psr7-minify-middleware
[Master image]: https://travis-ci.org/adriansuter/psr7-minify-middleware.svg?branch=master
