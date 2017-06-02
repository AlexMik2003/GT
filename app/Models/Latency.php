<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Latency extends Model
{
    /**
     * @var string
     */
    protected $table = "latency";

    /**
     * @var array
     */
    protected $fillable = [
        'device_id',
        'prev',
        'cur',
        'average',
        'status',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Add new device
     *
     * @param integer $device - device id
     */
    public static function addDevice($device)
    {
        self::create([
            "device_id" => $device,
            "previous" => 0,
            "current" => 0,
            "average" => 0,
            "status" => 0,
        ]);
    }

    /**
     * Update device latency
     *
     * @param integer $id - device id
     *
     * @param integer $prev - previous latency
     *
     * @param integer $cur - current latency
     *
     * @param integer $avg - average latency
     *
     * @param integer $status - status
     */
    public static function updateLatency($id,$prev,$cur,$avg,$status)
    {
        self::where("device_id","=",$id)->update([
            "prev" => $prev,
            "cur" => $cur,
            "average" => $avg,
            "status" => $status,
        ]);
    }

    /**
     * Delete latence of selected device
     *
     * @param integer $id - device id
     */
    public static function deleteLatency($id)
    {
        self::where("device_id",$id)->delete();
    }
}