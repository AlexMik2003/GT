<?php

namespace App\Controllers\Network;

use App\Controllers\BaseController;
use App\Models\ClientsNetwork;
use App\Models\Network;
use App\Models\PeersNetwork;

/**
 * Class NetworkInformController - detail information about network
 *
 * @package App\Controllers\Network
 */
class NetworkInformController extends BaseController
{
    /**
     * Show networkInform summary page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as network id
     *
     * @return mixed
     */
    public function pageNetworkSummary($request,$responce,$args)
    {
        $net = Network::getData($args["id"]);
        $hosts = $this->ip->hostCount($net->netmask)+2;
        $netPart = explode(".",$this->ip->convertlong2IP($net->network));
        $netPart = $netPart[0].".".$netPart[1].".".$netPart[2].".";

        $ip = [];
        for($i=0;$i<$hosts+1;$i++)
        {
            $ip[] = $netPart.$i;
        }

       $network = [];
       $clients = ClientsNetwork::getAllData();
       foreach ($clients as $item)
       {
           $network[] = [
               "network" => $this->ip->convertlong2IP($item->network),
               "count" =>   $this->ip->hostCount($item->mask)+2,
           ];
       }

       $peers = PeersNetwork::getAllData();
       foreach ($peers as $item)
        {
            $network[] = [
                "network" => $this->ip->convertlong2IP($item->network),
                "count" =>   $this->ip->hostCount($item->mask)+2,
            ];
        }

        $clientNetIndex = array_flip(array_column($network, 'network'));
        $columns = 16;
        $data = [];

        for ($i = 0; $i <= $hosts; $i++) {

            $ip = $netPart . $i;

            if ($i % $columns == 0) {
                $rowIndex = $i / $columns;
                if (!isset($data[$rowIndex])) {
                    $data[$rowIndex] = [];
                }

                $row = &$data[$rowIndex];
            }

            if (!isset($clientNetIndex[$ip])) {
                $row[] = ['ip' => $ip, 'color' => 'lime'];
            } else {
                $netKey = $clientNetIndex[$ip];
                $netData = $network[$netKey];

                for ($ip_client = 0; $ip_client < $netData['count']; $ip_client++) {
                    $counter = $i + $ip_client;

                    if ($counter % $columns == 0) {
                        $rowIndex = $counter / $columns;
                        if (!isset($data[$rowIndex])) {
                            $data[$rowIndex] = [];
                        }

                        $row = &$data[$rowIndex];
                    }

                    $color = ($ip_client == 0) ? 'red' : 'yellow';
                    $ip = $netPart . ($i + $ip_client);
                    $row[] = ['ip' => $ip, 'color' => $color];
                }

                $i = $counter;
            }
        }

        unset($row);
        return $this->view->render($responce, "/network/networkSummary.twig",["data" => $data]);
    }
}