<?php

namespace App\Helpers;

/**
 * Class IP
 *
 * @package App\Helpers
 */
class IP
{
    /**
     * IP mask
     */
    const NETMASK = '255.255.255.255';

    /**
     * @var array
     */
    protected $privateRange = [
        "A" => [
            "start" => "10.0.0.0",
            "end" => "10.255.255.255",
        ],
        "B" => [
            "start" => "172.16.0.0",
            "end" => "172.31.255.255",
        ],
        "C" => [
            "start" => "192.168.0.0",
            "end" => "192.168.255.255",
        ],
    ];


    /**
     * Convert ip address to long integer
     *
     * @param string $ip - ip address in string format with dot
     *
     * @return integer
     */
    public function convertIp2Long($ip)
    {
        return ip2long($ip);
    }

    /**
     * Convert long integer to ip address
     *
     * @param string $long - ip address in long integer format
     *
     * @return integer
     */
    public function convertLong2IP($long)
    {
        return long2ip($long);
    }

    /**
     * Convert netmask to a cidr mask
     *
     * @param integer $mask - ip mask in long integer
     *
     * @return integer
     */
    public function mask2Cidr($mask)
    {
        $base_mask = $this->convertIp2Long(self::NETMASK);
        return 32-log(($mask ^ $base_mask)+1,2);
    }

    /**
     * IP network class
     *
     * @param string $network - network
     *
     * @param string $mask - netmask
     *
     * @return string
     */
    public function ipClass($network,$netmask)
    {
        $class = '';
        $net = $this->IP2Bin($network);
        $mask = $this->IP2Bin($netmask);

        $res = ($net&$mask);

        if(substr($res,0,3)==='110')
        {
            $class = "C";
        }
        elseif(substr($res,0,2)==='10')
        {
            $class = "B";
        }
        elseif(substr($res,0,1)==='0')
        {
            $class = "A";
        }

        return $class;
    }

    /**
     * Convert ip addres to binary
     *
     * @param string $ip - ip address
     *
     * @return string
     */
    protected function IP2Bin($ip)
    {
        return sprintf("%032s",base_convert($this->convertIp2Long($ip),10,2));
    }


    /**
     * Get network type
     *
     * @param string $network - network
     *
     *
     * @return string
     */
    public function networkType($network,$netmask)
    {
        $type = 'Public';
        $class = $this->ipClass($network,$netmask);
        if(array_key_exists($class,$this->privateRange))
        {
            $start = $this->convertIp2Long($this->privateRange[$class]["start"]);
            $end = $this->convertIp2Long($this->privateRange[$class]["end"]);
            $net = $this->convertIp2Long($network);
            if($net>=$start and $net<=$end)
            {
                $type = "Private";
            }
        }

        return $type;
    }

    /**
     * Get count of hosts in network
     *
     * @param integer $netmask - ip netmask
     *
     * @return number
     */
    public function hostCount($netmask)
    {
        $hosts = 32-$this->mask2Cidr($netmask);
        return pow(2,$hosts)-2;
    }

    /**
     * Check if ip address in network range
     *
     * @param integer $network - network range
     *
     * @param integer $netmask - CIDR network range mask
     *
     * @param integer $ip - ip address
     *
     * @return bool
     */
    public function ip_in_range($network,$netmask,$ip)
    {
        $wildcard_mask = pow( 2, ( 32 - $netmask ) ) - 1;
        $mask = ~ $wildcard_mask;
        return ( ( $ip & $mask ) == ( $network & $mask ) );
    }
}