<?php

namespace App\Controllers\Map;

use App\Controllers\BaseController;
use App\Models\Area;
use App\Models\Device;

/**
 * Class MapController - show map
 *
 * @package App\Controllers\Map
 */
class MapController extends BaseController
{
    /**
     * Show map page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageMap($request,$responce)
    {
        return $this->view->render($responce, "/map/map.twig");
    }

    /**
     * Get data for map
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return json
     */
    public function showMap($request,$responce)
    {
        $area = [];
        $query = Area::get();
        foreach ($query as $item)
        {
            $device = Device::getDeviceCount($item->id);
            $address = $item->address.",".$item->city.",".$item->country;
            $coord = $this->getCoordinates($address);
            $coord = explode(",",$coord);
            $address = explode(",",$address);
            $area[] = [
                "area" => "Address: ".trim($address[0])."<br>Area: ".$item->area."<br>Device: ".$device,
                "lat" => floatval($coord[0]),
                "long" => floatval($coord[1]),
            ];
        }

        $json = json_encode($area);

        echo $json;
    }

    /**
     * Get coordinates by address
     *
     * @param string $address - address
     *
     * @return string
     */
    protected function getCoordinates($address){

        $address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern

        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";

        $response = file_get_contents($url);

        $json = json_decode($response,TRUE); //generate array object from the response from the web

        return ($json['results'][0]['geometry']['location']['lat'].",".$json['results'][0]['geometry']['location']['lng']);

    }
}