<?php

namespace App\Validator\Rules;

use App\Helpers\IP;
use App\Models\Device;
use \Respect\Validation\Rules\AbstractRule;

class DeviceIPAvailable extends AbstractRule
{
    public function validate($input)
    {
        $ip = new IP();
        return Device::where("ip_addr",$ip->convertIp2Long($input))->count()===0;
    }
}