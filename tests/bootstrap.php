<?php
/**
 * PSR7 Minify Middleware.
 *
 * @license https://github.com/adriansuter/psr7-minify-middleware/blob/master/LICENSE (MIT License)
 */

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
$autoloader->addPsr4('AdrianSuter\\PSR7\\Middleware\\Test\\', __DIR__);
