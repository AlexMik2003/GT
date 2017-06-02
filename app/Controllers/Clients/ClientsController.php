<?php

namespace App\Controllers\Clients;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\ClientsNetwork;
use App\Models\Users;
use App\Helpers\Session;
use \Respect\Validation\Validator as valid;

/**
 * Class ClientsController - for managing clients data
 *
 * @package App\Controllers\Clients
 */
class ClientsController extends BaseController
{
    /**
     * Show clients page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageClients($request,$responce)
    {
        return $this->view->render($responce, "/clients/clients.twig");
    }

    /**
     * Show page for adding new client
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageAddClient($request,$responce)
    {
        return $this->view->render($responce, "/clients/new.twig");
    }

    /**
     *
     * Create new client
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function addClient($request,$responce)
    {
        //validate client data
        $validation = $this->validator->validate($request,[
            'act_number' => valid::notEmpty()->stringType(),
            'client_name' => valid::notEmpty()->stringType(),
            'client_it_name' => valid::notEmpty()->stringType(),
            'client_address' => valid::notEmpty(),
            'client_sla' => valid::notEmpty(),
            'client_manager' => valid::notEmpty(),
            'client_net' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))->ClientsNetworkAvailable()),
            'client_mask' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("clients.new"));
        }

        //create new client
        $client = Clients::createClient($request->getParams());

        //adding networks for new client
        ClientsNetwork::createClientNetworks($client,$this->ip,$request->getParam('client_net'),$request->getParam('client_mask'),$request->getParam("hide_clients_net"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Create client", array("clietn" => $request->getParam("client_name"), "user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", "New client is created!");
        return $responce->withRedirect($this->router->pathFor("clients.management"));
    }

    /**
     * Get all clients from DB and decode to json
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return json
     */
    public function clientsList($request,$responce)
    {
        //order parametres
        $orderby = $request->getParam('order')[0]["column"];
        $sort['col'] = $request->getParam('columns')[$orderby]['data'];
        $sort['dir'] = $request->getParam('order')[0]["dir"];

        //search request
        $search = $request->getParam('search')['value'];

        //select rows from db by search request
        $query = Clients::with("clientsNetwork")->where('act_number', 'like', '%' . $search . '%')
            ->orWhere('client_name', 'like', '%' . $search . '%')
            ->orWhere('client_it_name', 'like', '%' . $search . '%')
            ->orWhere('client_address', 'like', '%' . $search . '%')
            ->orWhere('client_manager', 'like', '%' . $search . '%');

        //rows count
        $output['recordsTotal'] = $query->count();

        //get data from query request
        $output['data'] = $query->orderBy($sort["col"],$sort["dir"])->skip($request->getParam('start'))->take($request->getParam('length',10))->get();

        //data filter
        $output['recordsFiltered'] = $output['recordsTotal'];

        //draw parametr
        $output['draw'] = intval($request->getParam('draw'));

        //create array of area data
        foreach ($output["data"] as $key => $value)
        {
            $network = '';
            foreach ($value["clientsNetwork"] as $item)
            {
                $network .= $this->ip->convertLong2IP($item->network)."/".$this->ip->mask2Cidr($item->mask)."<br>";
            }
            $value["client_network"] = $network;
            switch ($value["client_sla"])
            {
                case "1":
                    $value["client_sla"] = "<span style='color:red;'><b>".$value['client_sla']."</b></span>";
                    break;
                case "2":
                    $value["client_sla"] = "<span style='color:darkorange;'><b>".$value['client_sla']."</b></span>";
                    break;
                case "3":
                    $value["client_sla"] = "<span style='color:limegreen;'><b>".$value['client_sla']."</b></span>";
                    break;
            }

        }

        //encode area data to json
        $json = json_encode($output);

        echo $json;

    }

    /**
     * Show page for editing client information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as client id
     *
     * @return mixed
     */
    public function pageEditClient($request,$responce,$args)
    {
        $client = Clients::getClientData($args["id"]);
        $clientNet = [];
        foreach ($client->clientsNetwork as $item)
        {
            $clientNet[] = [
              "net" => $this->ip->convertLong2IP($item->network),
               "mask" => $this->ip->convertLong2IP($item->mask),
            ];
        }

        return $this->view->render($responce, "/clients/edit.twig",["client" => $client,"clientNet" => $clientNet]);

    }

    /**
     * Edit and update client information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as client id
     *
     * @return mixed
     */
    public function editClient($request,$responce,$args)
    {
        //validate client data
        $validation = $this->validator->validate($request,[
            'act_number' => valid::notEmpty()->stringType(),
            'client_name' => valid::notEmpty()->stringType(),
            'client_it_name' => valid::notEmpty()->stringType(),
            'client_address' => valid::notEmpty(),
            'client_sla' => valid::notEmpty(),
            'client_manager' => valid::notEmpty(),
            'client_net' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))),
            'client_mask' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("clients.edit"));
        }

        //delete client networks
        ClientsNetwork::deleteClientNetworks($args["id"]);

        //update clients data
        Clients::updateClientData($args["id"],$request->getParams());

        //adding networks for new client
        ClientsNetwork::createClientNetworks($args["id"],$this->ip,$request->getParam('client_net'),$request->getParam('client_mask'),$request->getParam("hide_clients_net"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Change client data", array("client" => $request->getParam("client_name"), "user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("Client is updated!"));
        return $responce->withRedirect($this->router->pathFor("clients.management"));
    }

    /**
     * Delete selected client
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as client id
     *
     * @return mixed
     */
    public function deleteClient($request,$responce,$args)
    {
        $client = Clients::where("id","=",$args["id"])->first();
        Clients::deleteClient($args["id"]);
        ClientsNetwork::deleteClientNetworks($args["id"]);

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Delete client", array("client" => $client->client_name,"user" => $user->login));

        //create inform message
        $this->flash->addMessage("warning", strtoupper("Client is deleted!"));
        return $responce->withRedirect($this->router->pathFor("clients.management"));
    }


}