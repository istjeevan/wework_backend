<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesAdditionalOptions extends Model
{

    protected $table = 'properties_additional_options';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    
    public function options()
    {
        return $this->belongsTo('App\Models\AdditionalOptions','additional_option_id');
    }
}