<?php

namespace App\Validator\Rules;

use App\Models\Area;
use \Respect\Validation\Rules\AbstractRule;

class AreaAvailable extends AbstractRule
{
    public function validate($input)
    {
        return Area::where("area",$input)->count()===0;
    }
}