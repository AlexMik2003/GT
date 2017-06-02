<?php

require_once __DIR__."/../bootstrap/app.php";

use \App\Helpers\IP;
use \App\Models\Device;
use \JJG\Ping;
use  \App\Models\Latency;

$ip = new IP();
$ttl = 64;
$timeout = 5;

$device = Device::getDeviceDataForPing();

foreach ($device as $item)
{
    $ping = new Ping($ip->convertLong2IP($item['ip_addr']),$ttl,$timeout);
    $latency = $ping->ping();
    if ($latency !== false) {
        $status = 1;
        if($item['cur'] == 0){
            Latency::updateLatency($item['device_id'],0,$latency,0,$status);
        }
        else{
            Latency::updateLatency($item['device_id'],$item['cur'],$latency,($item['cur'] + $latency)/2,$status);
        }
    }
    else {
        $status = 0;
        Latency::updateLatency($item['device_id'],0,0,0,$status);
    }
}