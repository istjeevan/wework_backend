<?php

namespace App\Jobs;

use App\Events\Propertyevent;
use App\Models\AdditionalOptions;
use App\Models\Amenities;
use App\Models\ContractLengths;
use App\Models\LayoutDesigns;
use App\Models\NearByAmenities;
use App\Models\Properties;
use App\Models\PropertiesAdditionalOptions;
use App\Models\PropertiesAmenities;
use App\Models\PropertiesLayoutDesigns;
use App\Models\PropertiesNearByAmenities;
use App\Models\PropertyContractLengths;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class BulkUploadProperties implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $properties;

    protected $filetype;

    protected $userid;

    public $timeout = 0;

    public function __construct($properties, $filetype, $userid)
    {
        $this->properties = $properties;
        $this->filetype = $filetype;
        $this->userid = $userid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $skippedProperty = 0;

        $uploadedProperty = 0;

        $starttime = microtime(1);

        Log::info('Start uploading....');

        $init = array(
            'message' => __('0 of :count properties uploading...', ['count' => count($this->properties)]),
            'success' => true,
            'status' => 'uploading',
        );

        event(new Propertyevent($init, $this->userid));

        foreach ($this->properties as $pKey => $post) {

            $key = $pKey;

            $count = ($pKey + 1);

            try{
                if($this->filetype !== 'csv'){
                    $date = json_decode(json_encode($post['available_date']), true);
                }else{
                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', $post['available_date'])
                            ->format('Y-m-d');
                }
            }catch(\Exception $e){

                Log::error('Failed to parse date at row '.$count);
                Log::error($e->getMessage());

                $args = array(
                    'message' => 'Failed to parse date at row '.$count,
                    'success' => false,
                    'status' => 'uploading',
                );
        
                event(new Propertyevent($args, $this->userid));

                sleep(2);

                $this->delete();
            }

            $isPropertyExist = Properties::where(\DB::raw('BINARY `title`'), $post['title'])
                                ->where(\DB::raw('BINARY `location`'), $post['location'])
                                ->where('state', \DB::raw('BINARY `state`'), $post['state'])
                                ->where('pincode', $post['pincode'])
                                ->where('soft_deleted', '0')
                                ->first();

            if (!isset($isPropertyExist)) {

                Log::info('Property uploading -> ' . $key);

                DB::beginTransaction();

                array_push($isExistflag, 0);

                $latitude = 0;
                $longitude = 0;

                $prepAddr = str_replace(' ', '+', $post['location']);
                $prepAddr = str_replace('#', '%23', $prepAddr);

                $gMapApiKey = 'AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU';

                $geocode = @file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key='.$gMapApiKey);
                $output = json_decode($geocode);

                if (!$post['latitude'] && !$post['longitude']) {

                    if ($output->status == 'OK') {
                        // $getState = $output->results[0]->address_components[0]->long_name;
                        $latitude = sprintf("%.8f", $output->results[0]->geometry->location->lat);
                        $longitude = sprintf("%.8f", $output->results[0]->geometry->location->lng);
                    }

                } else {
                    $latitude = $post['latitude'];
                    $longitude = $post['longitude'];
                }

                $property = Properties::create(
                    [
                        'location' => $post['location'],
                        'pincode' => strip_tags($post['pincode']),
                        'state' => strip_tags($post['state']),
                        'latitude' => $latitude ?? 0,
                        'longitude' => $longitude ?? 0,
                        'price' => 0,
                        'capacity' => 0,
                        'title' => $post['title'],
                        'property_description' => isset($post['description']) ? $post['description'] : null,
                        'property_class' => isset($post['class_type']) ? $post['class_type'] : null,
                        'area' => isset($post['area']) ? $post['area'] : null,
                        'floors' => $post['floors'] ? $post['floors'] : NULL,
                        'available_from' => $this->filetype !== 'csv' ? date("Y-m-d", strtotime($date['date'])) : $date,
                        'is_available' => (isset($post['is_available']) && $post['is_available'] != null) ? $post['is_available'] : 1,
                        'thumbnail_image' => null,
                        'terms_and_condition_file' => null,
                        'created_at' => now(),
                        'default_contract_length' => 0,
                        'manager_name' => $post['manager_name'],
                        'manager_email' => $post['manager_email'],
                        'manager_phone_number' => $post['manager_phone_number'],
                        'property_video' => $post['video_url'] ?? null,
                    ]
                );

                if (isset($property)) {

                    try {

                        if ($post['amenties']) {

                            $amenities = explode(',', $post['amenties']);

                            foreach ($amenities as $am) {

                                $amenity = Amenities::where('uuid', '=', $am)
                                    ->orWhere(\DB::raw('BINARY `name`'), $am)
                                    ->first();

                                if (isset($amenity)) {

                                    PropertiesAmenities::create(
                                        [
                                            'property_id' => $property->id,
                                            'amenity_id' => $amenity->id,
                                            'created_at' => now(),
                                        ]
                                    );

                                }
                            }

                        }

                        if ($post['near_by_amenties']) {

                            $nearByAmenities = explode(',', $post['near_by_amenties']);

                            foreach ($nearByAmenities as $nKey => $nam) {

                                $nearByAmenity = NearByAmenities::where('uuid', '=', $nam)
                                    ->orWhere(\DB::raw('BINARY `name`'), $nam)
                                    ->first();

                                $nearByAmentiesDistance = explode(',', $post['near_by_amenties_distance']);

                                if (isset($nearByAmenity)) {
                                    PropertiesNearByAmenities::create(
                                        [
                                            'property_id' => $property->id,
                                            'near_by_amenity_id' => $nearByAmenity->id,
                                            'distance' => $post['near_by_amenties_distance'] && $nearByAmentiesDistance[$nKey] ? $nearByAmentiesDistance[$nKey] : 0,
                                            'created_at' => now(),
                                        ]
                                    );
                                }
                            }

                        }

                        if ($post['layout_design_uuid']) {

                            $layoutDesigns = explode(',', $post['layout_design_uuid']);

                            foreach ($layoutDesigns as $lKey => $ld) {

                                $layoutDesign = LayoutDesigns::where('uuid', '=', $ld)
                                    ->orWhere(\DB::raw('BINARY `name`'), $ld)
                                    ->first();

                                $layoutDesignCost = explode(',', $post['layout_design_cost']);
                                $layoutDesignHeadCount = explode(',', $post['layout_design_headcount']);

                                if (isset($layoutDesign)) {
                                    PropertiesLayoutDesigns::create(
                                        [
                                            'property_id' => $property->id,
                                            'layout_design_id' => $layoutDesign->id,
                                            'price' => $layoutDesignCost[$lKey] ?? 0,
                                            'capacity' => $layoutDesignHeadCount[$lKey] ?? 10,
                                            'is_default' => $lKey == 0 ? 1 : 0,
                                            'created_at' => now(),
                                        ]
                                    );
                                }
                            }

                        }

                        if ($post['contract_length_uuid']) {

                            $contractLengths = explode(',', $post['contract_length_uuid']);

                            foreach ($contractLengths as $cLKey => $cld) {

                                $contractLength = ContractLengths::where('uuid', '=', $cld)
                                    ->orWhere('length', '=', $cld)
                                    ->first();

                                $contractLengthPercent = explode(',', $post['contract_length_percent']);

                                if (isset($contractLength)) {
                                    PropertyContractLengths::create(
                                        [
                                            'property_id' => $property->id,
                                            'contract_length_id' => $contractLength->id,
                                            'percent' => $contractLengthPercent[$cLKey] ?? 0,
                                            'is_default' => $cLKey == 0 ? 1 : 0,
                                            'created_at' => now(),
                                        ]
                                    );
                                }
                            }

                        }

                        // additonal options store
                        if ($post['additional_option_uuid']) {

                            $additionalOptions = explode(',', $post['additional_option_uuid']);

                            foreach ($additionalOptions as $adKey => $ad) {

                                $additionalOption = AdditionalOptions::where('uuid', '=', $ad)
                                    ->orWhere(\DB::raw('BINARY `name`'), $ad)
                                    ->first();

                                $additionalOptionPrice = explode(',', $post['additional_option_price']);

                                if (isset($additionalOption)) {
                                    PropertiesAdditionalOptions::create(
                                        [
                                            'property_id' => $property->id,
                                            'additional_option_id' => $additionalOption->id,
                                            'price' => $additionalOptionPrice[$adKey] ?? 0,
                                            'created_at' => now(),
                                        ]
                                    );
                                }
                            }

                        }

                        /** Image upload using Google drive */

                            if($count == count($this->properties)){
                                $imageStatus = 'uploaded';
                            }else{
                                $imageStatus = 'uploading';
                            }

                            UploadGDriveFiles::dispatch($post,$property,$this->userid,$imageStatus);

                        /** End */

                        if ($count !== count($this->properties)) {
                            $args = array(
                                'message' => __(':no of :count properties uploaded', ['no' => ++$pKey, 'count' => count($this->properties)]),
                                'success' => true,
                                'status' => 'uploading',
                            );

                            event(new Propertyevent($args, $this->userid));
                        }

                        $uploadedProperty = $uploadedProperty + 1;

                        DB::commit();

                    } catch (Exception $e) {

                        DB::rollBack();
                        $count = ($pKey - 1);
                        $this->failed($e, $pKey);
                        break;
                    }

                }

            } else {

                /** If property exist */

                $skippedProperty = $skippedProperty + 1;

                $existMsg = array(
                    'message' => __('At row no. :row property ":title" already exist ! skipping ...', [
                        'row' => ++$pKey,
                        'title' => $post['title'],
                    ]),
                    'success' => true,
                    'status' => 'uploading',
                );

                event(new Propertyevent($existMsg, $this->userid));

                Log::info('Sleeping.for 3 sec if exist...');

                sleep(3);

            }

        }

        $endtime = round(microtime(1) - $starttime, 2);

        $endtime = round(($endtime / 60), 2);

        /** If all properties has been uploaded ! */

        if ($count == count($this->properties)) {

            $args = array(
                'message' => __(':uploaded of :all properties have been successfully uploaded and :skipped were skipped as they already exist.', [
                    'uploaded' => $uploadedProperty,
                    'all' => count($this->properties),
                    'skipped' => $skippedProperty,
                ]),
                'success' => true,
                'status' => 'uploaded',
            );

            event(new Propertyevent($args, $this->userid));

            Log::info('Properties imported successfully in ' . $endtime . ' minutes.');
        }

    }

    /**
     * Handle the failing job.
     *
     * @param Exception $ex
     *
     * @return void
     */

    public function failed(Exception $e, $count = 0)
    {
        $args = array(
            'message' => 'Error found at property row ' . ($count + 1) . '. Please check the entered data and reupload.',
            'success' => false,
            'status' => 'uploading',
        );

        event(new Propertyevent($args, $this->userid));

        Log::error('Property at row ' . ($count + 1) . ' insertion failed reason :' . $e->getMessage());
        $this->delete();
    }
}
