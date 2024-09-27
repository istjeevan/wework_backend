<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenities extends Model
{
    use SoftDeletes;

    protected $table = 'amenities';
    protected $guarded = [];

    public function properties()
    {
        return $this->belongsToMany('App\Models\Properties', 'properties_amenities', 'amenity_id', 'property_id');
	}

    public static function createUuid(){

        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789$'); 

        shuffle($seed); 
        $rand = '';
        foreach (array_rand($seed, 6) as $k) {
            $rand .= $seed[$k];
        }

        return \Str::upper($rand);
    }
}
