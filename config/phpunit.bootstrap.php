<?php
declare(strict_types = 1);

$loader = require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/test/Apha/StateStore/Storage/StateStorageTest.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);