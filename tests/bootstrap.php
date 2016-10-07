<?php

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
$autoloader->addPsr4('AdrianSuter\\PSR7\\Middleware\\Test\\', __DIR__);
