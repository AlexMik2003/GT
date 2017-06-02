<?php

namespace App\Validator\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class NetworkAvailableException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Network is already created!',
        ],
    ];
}