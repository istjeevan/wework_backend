<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesImages extends Model
{
    protected $table = 'properties_images';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}