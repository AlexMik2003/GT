<?php

namespace App\Validator\Rules;

use App\Models\Users;
use \Respect\Validation\Rules\AbstractRule;

class LoginAvailable extends AbstractRule
{
    public function validate($input)
    {
        return Users::where("login",$input)->count()===0;
    }
}