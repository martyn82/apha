<?php
declare(strict_types = 1);

namespace Apha\ReadStore\Storage;

class DocumentNotFoundException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private static $messageTemplate = "Document with identity '%s' not found.";

    /**
     * @param string $identity
     */
    public function __construct(string $identity)
    {
        parent::__construct(
            sprintf(
                static::$messageTemplate,
                $identity
            )
        );
    }
}