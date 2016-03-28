<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Message\Message;

interface ParameterResolver
{
    /**
     * @param Message $message
     * @param string $propertyName
     * @return mixed
     * @throws UnresolvableParameterException
     */
    public function resolveParameterValue(Message $message, string $propertyName);
}