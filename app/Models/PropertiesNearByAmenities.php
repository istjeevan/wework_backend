<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesNearByAmenities extends Model
{

    protected $table = 'properties_near_by_amenities';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function amenities()
    {
        return $this->belongsTo('App\Models\NearByAmenities','near_by_amenity_id');
    }
}