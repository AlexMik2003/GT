<?php

namespace App\Validator\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class ClientsNetworkAvailableException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Network is already used!',
        ],
    ];
}