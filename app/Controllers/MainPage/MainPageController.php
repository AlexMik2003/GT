<?php

namespace App\Controllers\MainPage;

use App\Controllers\BaseController;
use App\Models\Area;
use App\Models\Clients;
use App\Models\Device;
use App\Models\Latency;
use App\Models\Network;
use App\Models\Peers;
use App\Models\Users;

/**
 * Class MainPageController
 *
 * @package App\Controllers\MainPage
 */
class MainPageController extends BaseController
{
    /**
     * Show dashboard page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function index($request,$responce)
    {
        $clients = Clients::count();
        $peers = Peers::count();
        $networks = Network::count();
        $areas = Area::count();
        $device = Device::count();
        $deviceUp = 0;
        $deviceDown = 0;
        $deviceQuery = Device::with("latency")->get();
        foreach ($deviceQuery as $item)
        {
            if($item["latency"]["status"] == 1)
            {
                $deviceUp++;
            }
            else {
                $deviceDown++;
            }
        }
        $devices = "all: ".$device." | up: ".$deviceUp." | down: ".$deviceDown;
        $user = Users::count();
        $admin = 0;
        $no_admin = 0;
        $userQuery = Users::with("profile")->get();
        foreach ($userQuery as $item)
        {
            if($item["profile"]["privilege"] == 1)
            {
                $admin++;
            }
            else {
                $no_admin++;
            }
        }
        $users = "all: ".$user." | admins: ".$admin." | users: ".$no_admin;

        $data = [
            "clients" => $clients,
            "peers" => $peers,
            "networks" => $networks,
            "areas" => $areas,
            "devices" => $devices,
            "users" => $users,
        ];

        return $this->view->render($responce,"dashboard.twig",["data" => $data]);
    }

    /**
     * Get summary data and encode to json
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return JSON
     */
    public function Info($request,$responce)
    {
        $data = [];
        $latencyQuery = Latency::with("device")->orderBy("cur","DESC")->limit(5)->get();
        foreach ($latencyQuery as $item) {
            $data[] = [
                "host" => $item["device"]["name"],
                 "avg" => floatval($item["average"]),
                 "cur" => floatval($item["cur"]),
            ];
        }

        //encode data to json
        $json = json_encode($data);

        echo $json;
    }

}