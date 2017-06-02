<?php

namespace App\Controllers\Device;

use App\Controllers\BaseController;
use App\Models\Area;
use App\Models\Device;
use App\Models\Latency;
use App\Models\Users;
use App\Helpers\Session;
use \Respect\Validation\Validator as valid;

/**
 * Class DeviceController - for managing device
 *
 * @package App\Controllers\Device
 */
class DeviceController extends BaseController
{
    /**
     * Show device page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageDevice($request,$responce)
    {
        return $this->view->render($responce, "/device/device.twig");
    }

    /**
     * Show page for adding new device
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageAddDevice($request,$responce)
    {
        //get device parametres
        $model = $this->deviceConfig->getDevice();

        //get area list
        $area = Area::getArea();

        return $this->view->render($responce, "/device/new.twig",["area" => $area,"model" => $model]);
    }

    /**
     * Create new device
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function addDevice($request,$responce)
    {
        //validate data
        $validation = $this->validator->validate($request, [
            'device_ip' => valid::noWhitespace()->notEmpty()->ip()->DeviceIPAvailable(),
            'device_name' => valid::noWhitespace()->notEmpty(),
            'device_community' => valid::noWhitespace()->notEmpty()->stringType(),
            'device_vendor' => valid::notEmpty()->stringType(),
            'device_model' => valid::notEmpty()->stringType(),
            'device_area' => valid::notEmpty()->stringType(),
        ]);

        if ($validation->failed()) {
            return $responce->withRedirect($this->router->pathFor("device.new"));
        }

        //get area id
        $area = Area::getAreaId($request->getParam("device_area"));

        //create new device
        $device = Device::createDevice($this->ip,$request->getParams(),$area);

        //add device to latency table
        Latency::addDevice($device['id']);

        //get mibs for device
        $mibs = $this->deviceConfig->getDeviceMibs($device['vendor'],$device['model']);
        $portsArray = $this->deviceConfig->devicePorts($request->getParam('device_ip'),$device['community'],$mibs->ports);
        $vlansArray = $this->deviceConfig->deviceVlans($request->getParam('device_ip'),$device['community'],$mibs->vlans);

        //create array of device ports
        $ports = [];
        for($i=0;$i<count($portsArray["ports"]);$i++)
        {
           $ports[] = [
                    "ports_id" => $portsArray['ports'][$i],
                    "alias" => $portsArray["alias"][$i],
                    "desc" => $portsArray["desc"][$i],
                    "speed" => $portsArray["speed"][$i],
                    "operStatus" => $portsArray["operStatus"][$i],
                    "adminStatus" => $portsArray["adminStatus"][$i],
            ];

        }

        //create array fo mongo db
        $portsItems = [
            "device_id" => $device["id"],
            "ports" => $ports,
        ];

        $vlansItems = [
            "device_id" => $device["id"],
            "vlans" => $vlansArray,
        ];

        //insert data to mongo db
        $this->mongo->insertData("ports",$portsItems);
        $this->mongo->insertData("vlans",$vlansItems);

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Create device", array("device" => $request->getParam("device_name"),"user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("New device is created!"));
        return $responce->withRedirect($this->router->pathFor("device.management"));

    }

    public function deviceList($request,$responce)
    {
        //order parametres
        $orderby = $request->getParam('order')[0]["column"];
        $sort['col'] = $request->getParam('columns')[$orderby]['data'];
        $sort['dir'] = $request->getParam('order')[0]["dir"];

        //search request
        $search = $request->getParam('search')['value'];

        //select rows from db by search request
        $query = Device::with("area")->with("latency")->where('name', 'like', '%' . $search . '%');

        //rows count
        $output['recordsTotal'] = $query->count();

        //get data from query request
        $output['data'] = $query->orderBy($sort["col"], $sort["dir"])->skip($request->getParam('start'))->take($request->getParam('length', 10))->get();

        //data filter
        $output['recordsFiltered'] = $output['recordsTotal'];

        //draw parametr
        $output['draw'] = intval($request->getParam('draw'));

        //create array of device
        foreach ($output["data"] as $key => $value) {
            $dataLink = [
              "name" => $value["name"],
               "id" => $value["id"],
            ];
            $value["name"] = $dataLink;
            $value["ip"] = $this->ip->convertLong2IP($value["ip_addr"]);
            $value["area_name"] = $value["area"]["area"];
            $status = $value['latency']["status"];
            if($status == 1)
            {
                $value["status"] = "<span style='color:limegreen;'><b>UP</b></span>";
            }
            else   $value["status"] = "<span style='color:red;'><b>DOWN</b></span>";
        }

        //encode area data to json
        $json = json_encode($output);

        echo $json;
    }

    /**
     * Get action
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function getDeviceAction($request,$responce)
    {
        switch ($request->getParam("action"))
        {
            case "add":
                return $responce->withRedirect($this->router->pathFor("device.new"));
                break;
            case "del":
                $this->deleteDevice($request->getParam("device_id"));
                //create inform message
                $this->flash->addMessage("warning", strtoupper("Device is deleted!"));
                return $responce->withRedirect($this->router->pathFor("device.management"));
                break;
        }
    }

    /**
     * Delete selected device
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $id - array of device id
     *
     * @return mixed
     */

    public function deleteDevice($id)
    {
        foreach ($id as $key => $value)
        {
            $device  = Device::where("id","=",$value)->first();
            Device::deleteDevice($value);
            Latency::deleteLatency($value);

            $user = Users::getUser(Session::get("id"));
            //create log message
            $this->logger->info("Delete device", array("device" => $device->name,"user" => $user->login));

            $criteria = ["device_id" => intval($value)];
            $this->mongo->removeData("ports",$criteria);
            $this->mongo->removeData("vlans",$criteria);
        }

    }
}