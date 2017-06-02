<?php

namespace App\Validator\Rules;

use App\Helpers\IP;
use App\Models\Network;
use \Respect\Validation\Rules\AbstractRule;

class NetworkAvailable extends AbstractRule
{
    public function validate($input)
    {
        $ip = new IP();
        return Network::where("network",$ip->convertIp2Long($input))->count()===0;
    }
}