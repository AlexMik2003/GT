<?php

namespace App\Models;

use App\Helpers\IP;
use \Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * @var string
     */
    protected $table = "device";

    /**
     * @var array
     */
    protected $fillable = [
        'ip_addr',
        'name',
        'community',
        'vendor',
        'model',
        'area_id',
        'created',
        'updated',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latency()
    {
        return $this->hasOne(Latency::class);
    }

    /**
     * Create new device
     *
     * @param IP $ip
     *
     * @param array $data - device data
     *
     * @param integer $area - area id
     *
     * @return mixed
     */
    public static function createDevice($ip,$data,$area)
    {
        $device = self::create([
            "ip_addr" => $ip->convertIp2Long($data["device_ip"]),
            "name" => strtoupper($data["device_name"]),
            "community" => strtolower($data["device_community"]),
            "vendor" => strtolower($data["device_vendor"]),
            "model" => strtolower($data['device_model']),
            "area_id" => $area,
        ]);

        return [
                "id" => $device->id,
                "vendor" => $device->vendor,
                "model" => $device->model,
                "community" => $device->community,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get data about device for their ping
     *
     * @return array
     */
    public static function getDeviceDataForPing()
    {
        $device = [];
        $query = self::with("latency")->get();
        foreach ($query as $item)
        {
            $device[] =[
              "device_id" => $item->id,
               "ip_addr" => $item->ip_addr,
               "cur" =>  $item["latency"]["cur"],
            ];
        }

        return $device;
    }

    /**
     * Get device data
     *
     * @param integer $id - device id
     *
     * @return mixed
     */
    public static function getDeviceData($id)
    {
        return self::with("area")->where("id","=",$id)->first();
    }

    /**
     * Update device information
     *
     * @param integer $id - device id
     *
     * @param IP $ip
     *
     * @param array $data - device data
     *
     * @param integer $area - area id
     */
    public static function updateDeviceData($id,$ip,$data,$area)
    {
        self::where("id", "=", $id)->update([
            "ip_addr" => $ip->convertIp2Long($data["device_ip"]),
            "name" => strtoupper($data["device_name"]),
            "community" => strtolower($data["device_community"]),
            "vendor" => strtolower($data["device_vendor"]),
            "model" => strtolower($data['device_model']),
            "area_id" => $area,
        ]);
    }

    /**
     * Delete selected device
     *
     * @param integer $id - device id
     */
    public static function deleteDevice($id)
    {
        self::find($id)->delete();
    }

    /**
     * Get device count for area
     *
     * @param integer $id - area id
     *
     * @return mixed
     */
    public static function getDeviceCount($id)
    {
        return self::where("area_id","=",$id)->count();
    }

}