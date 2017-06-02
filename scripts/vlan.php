<?php

require_once __DIR__."/../bootstrap/app.php";

use \App\Models\Device;
use \App\Models\Vlan;
use \App\Controllers\Device\DeviceConfigController;
use \App\Helpers\IP;

$ip = new IP();
$device_config = new DeviceConfigController();
$device = Device::get();

foreach ($device as $item)
{
    $mibs = $device_config->getDeviceMibs($item->vendor, $item->model);
    $vlans = $device_config->deviceVlans($ip->convertLong2IP($item->ip_addr), $item->community, $mibs->vlans);
    foreach ($vlans as $value)
    {
        Vlan::where("id","=",$value["vlan_id"])->update([
            "vlan" => strtoupper($value["desc"]),
        ]);
    }

}
