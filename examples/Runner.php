<?php
declare(strict_types = 1);

namespace Apha\Examples;

/* @var $loader \Composer\Autoload\ClassLoader */
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4("Apha\\Examples\\", __DIR__ . "/");

AnnotationRegistry::registerLoader(function (string $className) use ($loader) {
    return $loader->loadClass($className);
});

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
