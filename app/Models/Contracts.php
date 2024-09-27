<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contracts extends Model
{
    use SoftDeletes;

    protected $table = 'contracts';
    protected $guarded = [];

    public function additional_options()
    {
        return $this->hasMany('App\Models\ContractsAdditionalOptions', 'contract_id');
    }
    public function property_details()
    {
        return $this->hasOne('App\Models\Properties', 'id','property_id');
    }
    public function user_details()
    {
        return $this->hasOne('App\Models\User','id' ,'user_id');
    }
}
