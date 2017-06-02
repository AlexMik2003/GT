<?php

namespace App\Controllers\Network;

use App\Controllers\BaseController;
use App\Models\ClientsNetwork;
use App\Models\Network;
use App\Models\PeersNetwork;
use App\Models\Users;
use App\Helpers\Session;
use \Respect\Validation\Validator as valid;

/**
 * Class NetworkController - for managing networks
 *
 * @package App\Controllers\Network
 */
class NetworkController extends BaseController
{
    /**
     * Show network page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageNetwork($request,$responce)
    {
        return $this->view->render($responce, "/network/network.twig");
    }

    /**
     * Show page for adding new network
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageAddNetwork($request,$responce)
    {
        return $this->view->render($responce, "/network/new.twig");
    }


    /**
     *
     * Create new network
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function addNetwork($request,$responce)
    {
        $validation = $this->validator->validate($request,[
            'ip_name' => valid::noWhitespace()->notEmpty()->NetworkNameAvailable(),
            'ip_net' => valid::notEmpty()->ip()->NetworkAvailable(),
            'ip_mask' => valid::notEmpty()->ip(),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("network.new"));
        }

        Network::createNetwork($this->ip,$request->getParams());

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Create network", array("network" =>$request->getParam("ip_name"),"user"=>$user->login));

        //create inform message
        $this->flash->addMessage("success", "New network is created!");
        return $responce->withRedirect($this->router->pathFor("network.management"));
    }

    /**
     * Get all networks from DB and decode to json
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return json
     */
    public function networkList($request,$responce)
    {
        //order parametres
        $orderby = $request->getParam('order')[0]["column"];
        $sort['col'] = $request->getParam('columns')[$orderby]['data'];
        $sort['dir'] = $request->getParam('order')[0]["dir"];

        //search request
        $search = $request->getParam('search')['value'];

        //select rows from db by search request
        $query = Network::where('name', 'like', '%' . $search . '%');

        //rows count
        $output['recordsTotal'] = $query->count();

        //get data from query request
        $output['data'] = $query->orderBy($sort["col"],$sort["dir"])->skip($request->getParam('start'))->take($request->getParam('length',10))->get();

        //data filter
        $output['recordsFiltered'] = $output['recordsTotal'];

        //draw parametr
        $output['draw'] = intval($request->getParam('draw'));

        $clientsNet = ClientsNetwork::getAllData();
        $peersNet = PeersNetwork::getAllData();
        $ips = [];
        //create array of area data
        foreach ($output["data"] as $key => $value)
        {
            $dataLink = [
                "network" => $this->ip->convertLong2IP($value["network"])."/".$this->ip->mask2Cidr($value["netmask"]),
                "id" => $value["id"],
            ];
            $value["class"] = $this->ip->ipClass($this->ip->convertLong2IP($value["network"]),$this->ip->convertLong2IP($value["netmask"]));
            $value["type"] = $this->ip->networkType($this->ip->convertLong2IP($value["network"]),$this->ip->convertLong2IP($value["netmask"]));
            $value["hosts"] = $this->ip->hostCount($value["netmask"]);
            $network = $value["network"];

            $netmask = $this->ip->mask2Cidr($value["netmask"]);
            foreach ($clientsNet as $item)
            {
                if($this->ip->ip_in_range($network,$netmask,$item->network))
                {
                   $ips[] =  $this->ip->hostCount($item->mask);
                }

            }
            foreach ($peersNet as $item)
            {
                if($this->ip->ip_in_range($network,$netmask,$item->network))
                {
                    $ips[] =  $this->ip->hostCount($item->mask);
                }

            }
            $used = number_format(array_sum($ips)*100/$value["hosts"],2);
            $free = 100 - $used;

            $value["free_used"] =  "Free: ".$free."<br>"."Used: ".$used;
            $value["network"] = $dataLink;
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
    public function getNetworkAction($request,$responce)
    {
        switch ($request->getParam("action"))
        {
            case "add":
                return $responce->withRedirect($this->router->pathFor("network.new"));
                break;
            case "del":
                $this->deleteNetwork($request->getParam("network_id"));
                $this->flash->addMessage("warning", strtoupper("Network is deleted!"));
                return $responce->withRedirect($this->router->pathFor("network.management"));
                break;
        }
    }

    /**
     * Delete selected network
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $id - array of networks id
     *
     * @return mixed
     */

    public function deleteNetwork($id)
    {
        foreach ($id as $key => $value)
        {
            $net  = Network::where("id","=",$value)->first();
            Network::deleteNetwork($value);

            //create log message
            $user = Users::getUser(Session::get("id"));
            $this->logger->info("Delete network", array("network" => $net->name,"user" => $user->login));
        }

    }
}