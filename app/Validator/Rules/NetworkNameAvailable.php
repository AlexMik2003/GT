<?php

namespace App\Validator\Rules;

use App\Models\Network;
use \Respect\Validation\Rules\AbstractRule;

class NetworkNameAvailable extends AbstractRule
{
    public function validate($input)
    {
        return Network::where("network",$input)->count()===0;
    }
}