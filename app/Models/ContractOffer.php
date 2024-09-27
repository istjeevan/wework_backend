<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractOffer extends Model
{
    use SoftDeletes;

    protected $table = 'contract_offer';
    protected $guarded = [];

    function contract_details(){
        return $this->hasOne('App\Models\Contracts', 'id','contract_id')->withTrashed();
    }
    function user_details(){
        return $this->hasOne('App\Models\User', 'id','user_id');
    }

   
}
