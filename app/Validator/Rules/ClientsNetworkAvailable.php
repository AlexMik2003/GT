<?php

namespace App\Validator\Rules;

use App\Helpers\IP;
use App\Models\ClientsNetwork;
use App\Models\PeersNetwork;
use \Respect\Validation\Rules\AbstractRule;

class ClientsNetworkAvailable extends AbstractRule
{
    public function validate($input)
    {
        $ip = new IP();
        $ips = [];
        $network = ClientsNetwork::getAllData();
        foreach ($network as $item)
        {
            $count = $ip->hostCount($item->mask)+2;
            $net_part = explode(".",$ip->convertLong2IP($item->network));
            for($i=$net_part[3];$i<($net_part[3]+$count);$i++)
            {
                $ips[] = $net_part[0].".".$net_part[1].".".$net_part[2].".".$i;
            }
        }
        $peer = PeersNetwork::getAllData();
        foreach ($peer as $item)
        {
            $count = $ip->hostCount($item->mask)+2;
            $net_part = explode(".",$ip->convertLong2IP($item->network));
            for($i=$net_part[3];$i<($net_part[3]+$count);$i++)
            {
                $ips[] = $net_part[0].".".$net_part[1].".".$net_part[2].".".$i;
            }
        }

        return in_array($input,$ips) ? false : true;
    }
}