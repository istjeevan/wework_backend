<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Properties extends Model {

    use SoftDeletes;

    protected $table = 'properties';
    protected $guarded = [];

    protected $appends = [ 'video_link', 'available_from' ];

    public function setAreaAttribute( $value ) {
        $this->attributes[ 'area' ] = ( int ) $value;
    }

    public function getAvailableFromAttribute() {
        return date( 'Y-m-d', strtotime( $this->attributes[ 'available_from' ] ) );
    }

    public function getAvailableEndAttribute() {
        return $this->attributes['available_end' ] ? date('Y-m-d', strtotime( $this->attributes[ 'available_end' ] )) : null ;
    }

    public function getVideoLinkAttribute() {

        if ( \Str::contains( $this->attributes[ 'property_video' ], [ 'http://', 'https://' ] ) ) {

            return $this->attributes[ 'property_video' ] = array(
                'type' => 'url',
                'url' => $this->attributes[ 'property_video' ]
            );

        } else {

            return $this->attributes[ 'property_video' ] = array(
                'type' => 'file',
                'url' => env( 'APP_URL' ).'/new_images/'.$this->attributes[ 'property_video' ]
            );

        }

    }

    public function setFloorsAttribute( $value ) {
        $this->attributes[ 'floors' ] = ( int ) $value;
    }

    public function contract_lengths() {
        return $this->hasMany( 'App\Models\PropertyContractLengths', 'property_id' );
    }

    public function amenities() {
        return $this->belongsToMany( 'App\Models\Amenities', 'properties_amenities', 'property_id', 'amenity_id' );
    }

    public function property_contractLength() {
        return $this->belongsToMany( 'App\Models\ContractLengths', 'properties_contract_lengths', 'property_id', 'contract_length_id' );
    }

    public function images() {
        return $this->hasMany( 'App\Models\PropertiesImages', 'property_id' );
    }

    public function nearByAmenities() {
        return $this->hasMany( 'App\Models\PropertiesNearByAmenities', 'property_id' );
    }

    public function layoutDesigns() {
        return $this->hasMany( 'App\Models\PropertiesLayoutDesigns', 'property_id' );
    }

    public function additional_options() {
        return $this->hasMany( 'App\Models\PropertiesAdditionalOptions', 'property_id' );
    }

    public function setContractTypeAttribute( $value ) {
        $this->attributes[ 'contract_type' ] = strtolower( $value );
    }

    // Handle max_contract_length_type independently
    public function setMaxContractLengthTypeAttribute($value) {
        $this->attributes['max_contract_length_type'] = strtolower($value);
    }

    public function setBuildingHeightAttribute( $value ) {
        $this->attributes[ 'building_height' ] = $value;
    }

    public function setBuildingSizeAttribute( $value ) {
        $this->attributes[ 'building_size' ] = ( int ) $value;
    }

    public function setAvailableEndAttribute( $value ) {
        $this->attributes[ 'available_end' ] = $value ?: null;
    }

}
