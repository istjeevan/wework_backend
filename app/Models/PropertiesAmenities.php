<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertiesAmenities extends Model
{

    protected $table = 'properties_amenities';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}