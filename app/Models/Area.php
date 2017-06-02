<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    /**
     * @var string
     */
    protected $table = "area";

    /**
     * @var array
     */
    protected $fillable = [
        'area',
        'address',
        'city',
        'country',
        'comments',
        'created',
        'updated',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contacts::class);
    }

    /**
     * Create new area and return id
     *
     * @param array $data - area data
     *
     * @return integer
     */
    public static function createArea($data)
    {
        $area = self::create([
            'area' => strtoupper($data["area_name"]),
            'address' => $data["address"],
            'city' => ucfirst($data["city"]),
            'country' => ucfirst($data["country"]),
            'comments' => $data["comments"],
        ]);

        return $area->id;
    }

    /**
     * Get area data
     *
     * @param integer $id - area id
     *
     * @return mixed
     */
    public static function getAreaData($id)
    {
        return self::with("contacts")->where("id","=",$id)->first();
    }

    /**
     * Update area data
     *
     * @param integer $id - area id
     *
     * @param array $data - area data
     */
    public static function updateAreaData($id,$data)
    {
        self::where("id","=",$id)->update([
            'area' => strtoupper($data["area_name"]),
            'address' => $data["address"],
            'city' => ucfirst($data["city"]),
            'country' => ucfirst($data["country"]),
            'comments' => $data["comments"],
        ]);
    }

    /**
     * Delete selected area
     *
     * @param integer $id - area id
     */
    public static function deleteArea($id)
    {
        self::find($id)->delete();
    }

    /**
     * Get area data
     *
     * @return array
     */
    public static function getArea()
    {
        $area = [];
        $query = self::get();
        foreach ($query as $item)
        {
            $area[$item->id] = $item->area;
        }

        return $area;
    }

    /**
     * Get area id by name
     *
     * @param string $name - area name
     *
     * @return integer
     */
    public static function getAreaId($name)
    {
        return self::where("area","=",$name)->first()->id;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function device()
    {
        return $this->hasMany(Device::class);
    }
}