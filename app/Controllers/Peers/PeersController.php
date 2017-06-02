<?php

namespace App\Controllers\Peers;

use App\Controllers\BaseController;
use App\Models\Peers;
use App\Models\PeersNetwork;
use App\Models\Users;
use App\Helpers\Session;
use \Respect\Validation\Validator as valid;

/**
 * Class PeersController - class for managing peers
 *
 * @package App\Controllers\Peers
 */
class PeersController extends BaseController
{
    /**
     * Show peers page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pagePeers($request,$responce)
    {
        return $this->view->render($responce, "/peers/peers.twig");
    }

    /**
     * Show page for adding new peer
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageAddPeer($request,$responce)
    {
        return $this->view->render($responce, "/peers/new.twig");
    }

    /**
     *
     * Create new peer
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function addPeer($request,$responce)
    {
        //validate peer data
        $validation = $this->validator->validate($request,[
            'peer_name' => valid::notEmpty()->stringType(),
            'peer_it_name' => valid::notEmpty()->stringType(),
            'peer_as' => valid::notEmpty()->intVal(),
            'peer_vlan' => valid::notEmpty()->intVal(),
            'peer_net' =>  valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))->ClientsNetworkAvailable()),
            'peer_mask' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("peers.new"));
        }

        //create new peer
        $peer = Peers::createPeer($request->getParams());

        //adding networks for new peer
        PeersNetwork::createPeerNetworks($peer,$this->ip,$request->getParam('peer_net'),$request->getParam('peer_mask'),$request->getParam("hide_peers_net"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Create peer", array("peer" => $request->getParam("peer_name"),"user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", "New peer is created!");
        return $responce->withRedirect($this->router->pathFor("peers.management"));
    }

    /**
     * Get all peers from DB and decode to json
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return json
     */
    public function peersList($request,$responce)
    {
        //order parametres
        $orderby = $request->getParam('order')[0]["column"];
        $sort['col'] = $request->getParam('columns')[$orderby]['data'];
        $sort['dir'] = $request->getParam('order')[0]["dir"];

        //search request
        $search = $request->getParam('search')['value'];

        //select rows from db by search request
        $query = Peers::with("peersNetwork")->where('peer_name', 'like', '%' . $search . '%')
            ->orWhere('peer_it_name', 'like', '%' . $search . '%')
            ->orWhere('peer_as', 'like', '%' . $search . '%');

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
            foreach ($value["peersNetwork"] as $item)
            {
                $network .= $this->ip->convertLong2IP($item->network)."/".$this->ip->mask2Cidr($item->mask)."<br>";
            }
            $value["peer_network"] = $network;

        }

        //encode area data to json
        $json = json_encode($output);

        echo $json;

    }

    /**
     * Show page for editing peer information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as peer id
     *
     * @return mixed
     */
    public function pageEditPeer($request,$responce,$args)
    {
        $peer = Peers::getPeerData($args["id"]);
        $peerNet = [];
        foreach ($peer->peersNetwork as $item)
        {
            $peerNet[] = [
                "net" => $this->ip->convertLong2IP($item->network),
                "mask" => $this->ip->convertLong2IP($item->mask),
            ];
        }

        return $this->view->render($responce, "/peers/edit.twig",["peer" => $peer,"peerNet" => $peerNet]);

    }

    /**
     * Edit and update peer information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as peer id
     *
     * @return mixed
     */
    public function editPeer($request,$responce,$args)
    {
        //validate peer data
        $validation = $this->validator->validate($request,[
            'peer_name' => valid::notEmpty()->stringType(),
            'peer_it_name' => valid::notEmpty()->stringType(),
            'peer_as' => valid::notEmpty()->intVal(),
            'peer_vlan' => valid::notEmpty()->intVal(),
            'peer_net' =>  valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))),
            'peer_mask' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::ip(),valid::equals(''))),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("peers.edit"));
        }

        //delete peer networks
        PeersNetwork::deletePeerNetworks($args["id"]);

        //update peers data
        Peers::updatePeerData($args["id"],$request->getParams());

        //adding networks for new peer
        PeersNetwork::createPeerNetworks($args["id"],$this->ip,$request->getParam('peer_net'),$request->getParam('peer_mask'),$request->getParam("hide_peers_net"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Change peer data", array("peer" => $request->getParam("peer_name"),"user"=>$user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("Peer is updated!"));
        return $responce->withRedirect($this->router->pathFor("peers.management"));
    }

    /**
     * Delete selected peer
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as peer id
     *
     * @return mixed
     */
    public function deletePeer($request,$responce,$args)
    {
        $peer = Peers::where("id","=",$args["id"])->first();
        Peers::deletePeer($args["id"]);
        PeersNetwork::deletePeerNetworks($args["id"]);

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Delete peer", array("peer" => $peer->peer_name,"user" => $user->login));

        //create inform message
        $this->flash->addMessage("warning", strtoupper("Peer is deleted!"));
        return $responce->withRedirect($this->router->pathFor("peers.management"));
    }

}