<?php
declare(strict_types = 1);

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = require __DIR__ . '/../vendor/autoload.php';

// register serializer annotations
\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);
