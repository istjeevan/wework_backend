<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractsAdditionalOptions extends Model
{
    use SoftDeletes;

    protected $table = 'contracts_additional_options';
    protected $guarded = [];

    public function options()
    {
        return $this->belongsTo('App\Models\AdditionalOptions','additional_options_id');
    }

}
