<?php
declare(strict_types = 1);

namespace Apha\Examples;

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = require_once __DIR__ . '/../../vendor/autoload.php';
$loader->addPsr4("Apha\\Examples\\", __DIR__ . "/");

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../../vendor/jms/serializer/src'
);

abstract class Runner
{
    /**
     * @return int
     */
    public static function main(): int
    {
        (new static())->run();
        return 0;
    }

    /**
     * @return void
     */
    abstract public function run();
}
