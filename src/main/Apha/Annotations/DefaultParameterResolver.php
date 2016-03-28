<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Message\Message;

class DefaultParameterResolver implements ParameterResolver
{
    /**
     * @param Message $message
     * @param string $propertyName
     * @return mixed
     * @throws UnresolvableParameterException
     */
    public function resolveParameterValue(Message $message, string $propertyName)
    {
        $reflectionObject = new \ReflectionObject($message);

        /* @var $property \ReflectionProperty */
        foreach ($reflectionObject->getProperties() as $property) {
            if ($property->getName() == $propertyName) {
                $property->setAccessible(true);
                return $property->getValue($message);
            }
        }

        throw new UnresolvableParameterException($message, $propertyName);
    }
}