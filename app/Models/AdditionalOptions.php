<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalOptions extends Model
{
    use SoftDeletes;

    protected $table = 'additional_options';
    protected $guarded = [];

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
