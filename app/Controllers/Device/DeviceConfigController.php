<?php

namespace App\Controllers\Device;


class DeviceConfigController
{
    /**
     * @var
     */
    protected $deviceConfig;

    /**
     * DeviceConfigController constructor.
     */
    public function __construct()
    {
        $configFile = ROOT_PATH."/data/config.json";

        $configFile = file_get_contents($configFile);

        if (empty($configFile)) {
            throw new \BadMethodCallException('Configuration file is empty.');
        }

        $this->deviceConfig = json_decode($configFile);
    }

    /**
     * Get device models and vendors
     *
     * @return array
     */
    public function getDevice()
    {
        $device = [];
        foreach ($this->deviceConfig as $key => $value)
        {
            $device[] = [
                "vendor" => $key,
                "model" => $value->model,
            ];
        }

        return $device;
    }

    /**
     * Get mibs for device from config
     *
     * @param string $vendor - device vendor
     *
     * @param string $model - device model
     *
     * @return array
     */
    public function getDeviceMibs($vendor,$model)
    {
        $mibs = [];
        foreach ($this->deviceConfig as $key => $value)
        {
            if($vendor == $key && $model == $value->model)
            {
                $mibs = $value->mibs;
            }
        }

        return $mibs;
    }

    /**
     * Device system information
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - system mib
     *
     * @return array
     */
    public function deviceSystemInfo($ip,$community,$mib)
    {
        $system = [];
        foreach ($mib as $key => $value)
        {
            switch ($key)
            {
                case "systemInfo":
                    $snmp = $this->systemInfo($ip,$community,$value);
                    break;
                case "hostname":
                    $snmp = $this->deviceHostname($ip,$community,$value);
                    break;
                case "uptime":
                    $snmp = $this->deviceUptime($ip,$community,$value);
                    break;
            }
            $system[$key] = $snmp;
        }
        return $system;
    }

    /**
     * Device gauge information
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - gauge mib
     *
     * @param string $vendor - device vendor
     *
     * @return array
     */
    public function deviceGaugeInfo($ip,$community,$mib,$vendor)
    {
        $gauge = [];
        foreach ($mib as $key => $value)
        {
            switch ($key)
            {
                case "cpu":
                    $snmp = $this->deviceCPU($ip,$community,$value);
                    break;
                case "memory":
                    $snmp = $this->deviceMemory($ip,$community,$value,$vendor);
                    break;
            }
            $gauge[$key] = $snmp;
        }
        return $gauge;
    }

    /**
     * Get system device information
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - systemInfo mib
     *
     * @return mixed
     */
    protected function systemInfo($ip,$community,$mib)
    {
        $system = snmp2_walk($ip,$community,$mib);
        $system = explode("STRING:",$system[0]);
        $system = str_replace('"', "", $system[1]);
        return trim($system);
    }

    /**
     * Get device hostname
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - hostname mib
     *
     * @return mixed
     */
    protected function deviceHostname($ip,$community,$mib)
    {
        $hostname = snmp2_walk($ip,$community,$mib);
        $hostname = explode("STRING:",$hostname[0]);
        $hostname = str_replace('"', "", $hostname[1]);
        return trim($hostname);
    }

    /**
     * Device uptime
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - uptime mib
     *
     * @return mixed
     */
    protected function deviceUptime($ip,$community,$mib)
    {
        $uptime = snmp2_walk($ip,$community,$mib);
        return $uptime;
    }

    /**
     * Device cpu information
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - cpu mib
     *
     * @return mixed
     */
    protected function deviceCPU($ip,$community, $mib)
    {
        $cpu = snmp2_walk($ip,$community,$mib);
        $cpu =  explode(" ",$cpu[0]);
        $cpu =  str_replace('"', "", $cpu[1]);
        return trim($cpu);
    }

    /**
     * Device memory utilization
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - memory mib
     *
     * @param string $vendor - device vendor
     *
     * @return mixed
     */
    protected function deviceMemory($ip,$community, $mib, $vendor)
    {
       if($vendor == 'cisco')
       {
          $used = snmp2_walk($ip,$community,$mib->used);
          $used = explode(" ",$used[0]);
          $free = snmp2_walk($ip,$community,$mib->free);
          $free = explode(" ",$free[0]);
          $total = $used[1] + $free[1];
          $memory = $used[1] * 100 / $total;
       }
       if($vendor == 'huawei')
       {
           $used = snmp2_walk($ip,$community,$mib->used);
           $used = explode(" ",$used[0]);
           $total = snmp2_walk($ip,$community,$mib->total);
           $total = explode(" ",$total[0]);
           $memory = $used[1] * 100 / $total[1];
       }

       return intval($memory);
    }

    /**
     * Get device ports
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - ports mib
     *
     * @return array
     */
    public function devicePorts($ip, $community, $mib)
    {
        $count = $this->portsCount($ip, $community, $mib->count);
        $portsNum = $this->getPortsNumber($ip, $community, $mib->port_number,$count);
        $ports = $this->getPortsType($ip, $community, $mib->type,$portsNum);
        $alias = $this->getPortsAlias($ip, $community, $mib->alias,$ports);
        $desc = $this->getPortsDescription($ip, $community, $mib->desc,$ports);
        $speed = $this->getPortsSpeed($ip, $community, $mib->speed,$ports);
        $operStatus = $this->getPortsOperStatus($ip, $community, $mib->oper_status,$ports);
        $adminStatus = $this->getPortsAdminStatus($ip, $community, $mib->admin_status,$ports);

        return $portsArray = [
            "ports" => $ports,
            "alias" => $alias,
            "desc" => $desc,
            "speed" => $speed,
            "operStatus" => $operStatus,
            "adminStatus" => $adminStatus,
        ];
    }

    /**
     * Device port count
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - count mib
     *
     * @return mixed
     */
    protected function portsCount($ip, $community, $mib)
    {
        $count = snmp2_walk($ip,$community,$mib);
        $count = explode(" ",$count[0]);
        return $count[1];
    }

    /**
     * Get array of ports numbers
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port number mib
     *
     * @param integer $count - ports count
     *
     * @return array
     */
    protected function getPortsNumber($ip, $community, $mib,$count)
    {
        $portsNum = [];
        $portMib = $mib."0";

        for($i=0;$i<intval($count);$i++)
        {
            $port = snmp2_getnext($ip, $community, $portMib);
            $port = explode(" ",$port);
            $port = trim($port[1]);
            array_push($portsNum,$port);
            $portMib = $mib.$port;
        }

        return $portsNum;
    }

    /**
     * Get type of ports
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port type mib
     *
     * @param array $ports - device ports
     *
     * @return array
     */
    protected function getPortsType($ip, $community, $mib, $ports)
    {
        foreach ($ports as $item)
        {
            $type = snmp2_walk($ip,$community,$mib.$item);
            $type = explode(" ",$type[0]);
            if(intval($type[1])!=6)
            {
               unset($item);
            }
        }

        return $ports;
    }

    /**
     * Get ports alias
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port alias mib
     *
     * @param array $ports - device ports
     *
     * @return array
     */
    protected function getPortsAlias($ip, $community, $mib, $ports)
    {
        $portsAlias = [];
        foreach ($ports as $item)
        {
            $alias = snmp2_walk($ip,$community,$mib.$item);
            $alias = explode(" ",$alias[0]);
            $alias = str_replace('"', "", $alias[1]);
            array_push($portsAlias,$alias);
        }

        return $portsAlias;
    }

    /**
     * Get ports description
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port description mib
     *
     * @param array $ports - device ports
     *
     * @return array
     */
    protected function getPortsDescription($ip, $community, $mib, $ports)
    {
        $portsDesc = [];

        foreach ($ports as $item)
        {
            $desc = snmp2_walk($ip,$community,$mib.$item);
            $desc = explode("STRING:",$desc[0]);
            $desc = !empty($desc[1]) ? str_replace('"', "", $desc[1]) : null;
            array_push($portsDesc,trim($desc));
        }

        return $portsDesc;
    }

    /**
     * Get ports speed
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port speed mib
     *
     * @param array $ports - device ports
     *
     * @return array
     */
    protected function getPortsSpeed($ip, $community, $mib, $ports)
    {
        $portsSpeed = [];
        foreach ($ports as $item)
        {
            $speed = snmp2_walk($ip,$community,$mib.$item);
            $speed = explode(" ",$speed[0]);
            array_push($portsSpeed,intval($speed[1]));
        }

        return $portsSpeed;
    }

    /**
     * Get port oper status
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port oper status mib
     *
     * @param array $ports - device ports
     *
     * @return array
     */
    protected function getPortsOperStatus($ip, $community, $mib, $ports)
    {
        $operStatus = [];
        foreach ($ports as $item)
        {
            $oper = snmp2_walk($ip,$community,$mib.$item);
            $oper = explode(" ",$oper[0]);
            array_push($operStatus,$oper[1]);
        }

        return $operStatus;
    }

    /**
     * Get port admin status
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - port admin status mib
     *
     * @param array $ports - device ports
     *
     * @return array
     */
    protected function getPortsAdminStatus($ip, $community, $mib, $ports)
    {
        $adminStatus = [];
        foreach ($ports as $item)
        {
            $admin = snmp2_walk($ip,$community,$mib.$item);
            $admin = explode(" ",$admin[0]);
            array_push($adminStatus,$admin[1]);
        }

        return $adminStatus;
    }

    /**
     * Get device vlans
     *
     * @param string $ip - device ip address
     *
     * @param string $community - snmp community
     *
     * @param string $mib - vlans mib
     *
     * @return array
     */
    public function deviceVlans($ip, $community, $mib)
    {
        $snmpArray = snmpwalkoid($ip,$community,$mib->vlan);
        $vlans = [];
        foreach ($snmpArray as $key => $value)
        {
            $key = explode(".",$key);
            $value = explode(" ",$value);
            $vlans[] = [
              "vlan_id" => $key[count($key)-1],
               "desc" => str_replace('"', "", $value[1]),
            ];
        }

        return $vlans;
    }
}