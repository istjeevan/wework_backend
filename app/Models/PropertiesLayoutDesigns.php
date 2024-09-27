<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesLayoutDesigns extends Model
{
    protected $table = 'properties_layout_designs';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    
    public function layoutDesign()
    {
        return $this->belongsTo('App\Models\LayoutDesigns','layout_design_id');
    }

}
