<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyContractLengths extends Model
{

    protected $table = 'properties_contract_lengths';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function contract_length()
    {
        return $this->belongsTo('App\Models\ContractLengths', 'contract_length_id');
    }

    public function getPercent($propertyId, $contractLengthId)
    {
        $value = $this->where(['property_id' => $propertyId, 'id' => $contractLengthId])->first();
        return $value->percent;
    }
}
