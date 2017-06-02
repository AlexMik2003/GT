<?php

namespace App\Validator\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class DeviceIPAvailableException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Device is already created!',
        ],
    ];
}