<?php

namespace App\Controllers\Device;

use App\Controllers\BaseController;
use App\Models\Device;

/**
 * Class DeviceInformController - class for monitoring and managing
 *                                information about selected device
 *
 * @package App\Controllers\Device
 */
class DeviceInformController extends BaseController
{
    /**
     * Show deviceInform summary page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as device id
     *
     * @return mixed
     */
    public function pageDeviceSummary($request,$responce,$args)
    {
        //get device data by id
        $device = Device::getDeviceData($args["id"]);
        //convert ip address
        $ip_addr = $this->ip->convertLong2IP($device->ip_addr);
        //create criteria for searching in mongo db
        $criteria = ["device_id" => intval($args["id"])];
        //get ports device data from mongo db
        $portsArray = $this->mongo->getData("ports",$criteria);
        $vlansArray = $this->mongo->getData("vlans",$criteria);

        //create array
        $ports = [];
        foreach ($portsArray as $item)
        {
            $ports = $item['ports'];
        }

        $vlans = [];
        foreach ($vlansArray as $item)
        {
            $vlans = $item['vlans'];
        }

        return $this->view->render($responce, "/device/deviceSummary.twig",[
            "device" => $device,
            "ip_addr" => $ip_addr,
            "ports" => $ports,
            "vlans" => $vlans,
        ]);
    }

    /**
     * Get device gauge information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as device id
     *
     * @return mixed
     */
    public function deviceSNMP($request,$responce,$args)
    {
        $device = Device::getDeviceData($args["id"]);
        $ip_addr = $this->ip->convertLong2IP($device->ip_addr);

        $mibs = $this->deviceConfig->getDeviceMibs($device->vendor,$device->model);
        $system = $this->deviceConfig->deviceSystemInfo($ip_addr,$device->community,$mibs->system);
        $gauge = $this->deviceConfig->deviceGaugeInfo($ip_addr,$device->community,$mibs->gauge,$device->vendor);

        $data = [
            "system" => $system,
            "gauge" => $gauge,
        ];

        $json = json_encode($data);

        echo $json;
    }

    public function interfaceInfo($request,$responce,$args)
    {

    }

}