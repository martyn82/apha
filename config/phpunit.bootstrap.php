<?php
declare(strict_types = 1);

error_reporting(E_ALL);

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = require __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function (string $className) use ($loader) {
    return $loader->loadClass($className);
});
