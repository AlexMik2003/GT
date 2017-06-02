<?php

namespace App\Validator\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class AreaAvailableException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Area is already created!',
        ],
    ];
}