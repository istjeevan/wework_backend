<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NearByAmenities extends Model
{
    use SoftDeletes;

    protected $table = 'near_by_amenities';

    protected $guarded = [''];

    public function properties()
    {
        return $this->belongsToMany('App\Models\Properties', 'properties_near_by_amenities_table', 'near_by_amenity_id', 'property_id');
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
