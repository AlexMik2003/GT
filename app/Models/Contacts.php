<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    /**
     * @var string
     */
    protected $table = "contacts";

    /**
     * @var array
     */
    protected $fillable = [
        'area_id',
        'name',
        'phone',
        'mail',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Create area contacts for new area
     *
     * @param integer $id - area id
     *
     * @param array $name - contacts names
     *
     * @param array $phone - contacts phones
     *
     * @param array $mail - contacts mails
     *
     * @param integer $count - count of contacts
     */
    public static function createContacts($id,$name,$phone,$mail,$count)
    {
        for($i=0;$i<$count;$i++)
        {
           self::create([
                'area_id' => $id,
                'name' => $name[$i],
                'phone' => $phone[$i],
                'mail' => $mail[$i],
            ]);
        }
    }

    /**
     * Delete contacts for selected area
     *
     * @param integer $value - area id
     */
    public static function deleteAreaContacts($id)
    {
        self::where("area_id",$id)->delete();
    }
}