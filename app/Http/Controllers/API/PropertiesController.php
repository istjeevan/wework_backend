<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Jobs\BulkUploadProperties;
use App\Models\Amenities;
use App\Models\Contracts;
use App\Models\LayoutDesigns;
use App\Models\NearByAmenities;
use App\Models\Properties;
use App\Models\PropertiesAdditionalOptions;
use App\Models\PropertiesAmenities;
use App\Models\PropertiesImages;
use App\Models\PropertiesLayoutDesigns;
use App\Models\PropertiesNearByAmenities;
use App\Models\PropertyContractLengths;
use Carbon\Carbon;
use File;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;

class PropertiesController extends BaseController
{
    public function __construct()
    {
        // no code
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->get('perPage') == "all") {
            $properties = Properties::with('contract_lengths')->with('amenities')->where('soft_deleted', 0)->get();
        } else {
            if ($request->get('search') != '') {
                $properties = Properties::with('contract_lengths')
                    ->with('amenities')
                    ->where('soft_deleted', 0)
                    ->where('location', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('pincode', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('state', 'like', '%' . $request->get('search') . '%')
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('perPage'));
            } else {
                $properties = Properties::with('contract_lengths')->with('amenities')->where('soft_deleted', 0)->orderBy('created_at', 'desc')->paginate($request->get('perPage'));
            }
        }
        return $this->sendResponse($properties->toArray(), 'Properties fetched successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $post = $request->all();
        $validator = Validator::make($post, [
            'location' => 'required',
            // 'price' => 'required',
            // 'capacity' => 'required',
            // 'area' => 'required',
            'available_from' => 'required',
            'thumbnail' => 'required|mimes:jpeg,png,jpg,bmp,gif,svg',
            'attachments.*' => 'required|mimes:jpeg,png,jpg,bmp,gif,svg',
            'conditionFile' => 'required|mimes:pdf',
            'selectedAmenity' => 'required',
            'selectedContract' => 'required',
            // 'default_contract_length' => 'required',
            'manager_name' => 'required',
            'manager_email' => 'required|email',
            'manager_phone_number' => 'required',
            'manager_image' => 'nullable|mimes:jpeg,png,jpg,bmp,gif,svg',
            'title' => 'required',
            'pincode' => 'required|numeric',
            'country' => 'required',
            'min_price' => 'required|numeric',
            'max_price' => 'required|numeric',
            'available_end' => 'nullable|string|max:50',
            'min_length'=>'required',
            'contract_type' => 'nullable|string|max:10',
            'max_contract_length_type' => 'nullable|string|max:10',
            'building_height' => 'nullable|string|max:10',
            'building_size' => 'nullable|numeric',

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // for thumbnail image upload
        $imageName = 'thumbnail_' . time() . '.' . $request->thumbnail->getClientOriginalExtension();

        $request->thumbnail->move(public_path('new_images'), $imageName);

        // for conditional file upload
        $file = 'conditionFile_' . time() . '.' . $request->conditionFile->getClientOriginalExtension();

        $request->conditionFile->move(public_path('terms_and_condition'), $file);

        //for manager_image file upload
        $manager_image = '';
        if (isset($request->manager_image)) {
            $manager_image = 'manager_' . time() . '.' . $request->manager_image->getClientOriginalExtension();
            $request->manager_image->move(public_path('new_images'), $manager_image);
        }
        //for property video
        $property_video = null;

        if (isset($request->property_video)) {

            /** if video upload selected */

            if ($request->hasFile('property_video')) {

                $request->validate([
                    'property_video' => 'mimes:mp4,webm,flv,wmv,3gp,mov|max:20480',
                ], [
                    'property_video.max' => 'Property video may not be greater then 20MB',
                ]);

                $property_video = time() . '.' . $request->property_video->getClientOriginalExtension();
                $request->property_video->move(public_path('new_images'), $property_video);
            }

            /** If url selected */

            if (!$request->hasFile('property_video') && $request->property_video) {
                $property_video = $request->property_video;
            }

        }

        $property = Properties::create(
            [
                'location' => $post['location'],
                'price' => $post['price'] ?? 0,
                'capacity' => $post['capacity'] ?? 0,
                'title' => $post['title'],
                'property_description' => isset($post['property_description']) ? $post['property_description'] : null,
                'property_class' => isset($post['property_class']) ? $post['property_class'] : null,
                'area' => isset($post['area']) ? $post['area'] : null,
                'floors' => isset($post['floors']) ? $post['floors'] : null,
                'latitude' => $post['latitude'],
                'longitude' => $post['longitude'],
                'available_from' => date("Y-m-d", strtotime($post['available_from'])),
                'available_end' => isset($post['available_end']) && $post['available_end'] != '' && $post['available_end'] != 'null' ? date("Y-m-d", strtotime($post['available_end'])) : null,
                'is_available' =>  1,
                'thumbnail_image' => $imageName,
                'terms_and_condition_file' => $file,
                'created_at' => now(),
                'default_contract_length' => $post['default_contract_length'] ?? 0,
                'manager_name' => $post['manager_name'],
                'manager_email' => $post['manager_email'],
                'manager_phone_number' => $post['manager_phone_number'],
                'manager_image' => $manager_image,
                'property_video' => $property_video,
                'pincode' => strip_tags($request->pincode),
                'state' => strip_tags($request->state),
                'country' => strip_tags($request->country),
                'min_price' => $post['min_price'],
                'max_price' => $post['max_price'],
                'max_length' => is_null($post['max_length']) ? Null : $post['max_length'],
                'min_length' => $post['min_length'],
                'contract_type' => isset($post['contract_type']) ? $post['contract_type'] : null,
                'max_contract_length_type' => isset($post['max_contract_length_type']) ? $post['max_contract_length_type'] : null,
                'building_height' => isset($post['building_height']) ? $post['building_height'] : null,
                'building_size' => isset($post['building_size']) ? $post['building_size'] : null,

            ]
        );

        if ($property) {

            // for multiple images upload
            $files = $request->file('attachments');
            foreach ($files as $key => $value) {
                $image = time() . '.' . $value->getClientOriginalName();

                $value->move(public_path('new_images'), $image);

                $propertyImage = PropertiesImages::create(
                    [
                        'property_id' => $property->id,
                        'image_name' => $image,
                        'created_at' => now(),
                    ]
                );
            }

            // amenities store
            $amenities = json_decode($post['selectedAmenity']);
            foreach ($amenities as $key => $value) {
                $propertyAmenities = PropertiesAmenities::create(
                    [
                        'property_id' => $property->id,
                        'amenity_id' => $value->id,
                        'created_at' => now(),
                    ]
                );
            }

            // contract length store
            $contracts = json_decode($post['selectedContract']);
            foreach ($contracts as $key => $value) {
                $propertyContracts = PropertyContractLengths::create(
                    [
                        'property_id' => $property->id,
                        'contract_length_id' => $value->id,
                        'percent' => $value->percent ?? 0,
                        'is_default' => $value->is_default ?? 0,
                        'created_at' => now(),
                    ]
                );
            }

            // near by amenities store
            if (isset($post['nearByAmenities'])) {
                $nearByAmenities = json_decode($post['nearByAmenities']);
                foreach ($nearByAmenities as $key => $value) {
                    $propertyNearByAmenities = PropertiesNearByAmenities::create(
                        [
                            'property_id' => $property->id,
                            'near_by_amenity_id' => $value->id,
                            'distance' => $value->dist,
                            'created_at' => now(),
                        ]
                    );
                }
            }

            // additonal options store
            if (isset($post['additional_options'])) {
                $additional_options = json_decode($post['additional_options']);
                foreach ($additional_options as $key => $value) {
                    $additional_options = PropertiesAdditionalOptions::create(
                        [
                            'property_id' => $property->id,
                            'additional_option_id' => $value->id,
                            'price' => $value->price ?? 0,
                            'created_at' => now(),
                        ]
                    );
                }
            }

            // layout design store
            $layoutDesign = json_decode($request->layoutDesigns);
            foreach ($layoutDesign as $key => $value) {
                $propertyLayoutDesign = PropertiesLayoutDesigns::create(
                    [
                        'property_id' => $property->id,
                        'layout_design_id' => $value->id,
                        'price' => $value->price ?? 0,
                        'capacity' => $value->capacity,
                        'is_default' => $value->is_default ?? 0,
                        'created_at' => now(),
                    ]
                );
            }

            // $propertyContactLength =
            $properties = Properties::with('contract_lengths')->with('amenities')->paginate(5);
            return $this->sendResponse($properties, 'Property created successfully');
        } else {
            $filename = public_path() . '/new_images/' . $imageName;
            // $filename = public_path('/new_images/'.$imageName);
            \File::delete($filename);

            $filename = public_path() . '/terms_and_condition/' . $file;
            \File::delete($filename);

            return $this->sendError('Error', 'Error in entering property', 402);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $propertyData = Properties::where('id', $id)
            ->with('contract_lengths.contract_length')
            ->with('amenities')
            ->with('layoutDesigns.layoutDesign')
            ->with('images')
            ->with('nearByAmenities.amenities')
            ->with('additional_options.options')
            ->first();

        if ($propertyData) {
            $propertyData->thumbnail_image = "/new_images/" . $propertyData->thumbnail_image;
            // $propertyData->thumbnail_image = public_path('/new_images/'.$propertyData->thumbnail_image);
            $propertyData->terms_and_condition_file = "/terms_and_condition/" . $propertyData->terms_and_condition_file;
            $propertyData->manager_image = "/new_images/" . $propertyData->manager_image;
            foreach ($propertyData->images as $key => $value) {
                $propertyData->images[$key]->image_name = "/new_images/" . $value->image_name;
                // $propertyData->images[$key]->image_name = public_path('/new_images/'.$value->image_name);
            }
            // if (!empty($propertyData->property_video)) {
            //     $propertyData->property_video = "/new_images/" . $propertyData->property_video;
            // }
            return $this->sendResponse($propertyData, 'Property fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = $request->all();
        $property = Properties::findOrFail($id); //Get content category specified by id

        if ($property) {

            $validator = Validator::make($post, [
                'location' => 'required',
                'price' => 'required',
                'capacity' => 'required',
                // 'area' => 'required',
                'available_from' => 'required',
                'selectedAmenity' => 'required',
                'layoutDesigns' => 'required',
                'selectedContract' => 'required',
                // 'default_contract_length' => 'required',
                'pincode' => 'required|numeric',
                'contract_type' => 'nullable|string|max:10',
                'max_contract_length_type' => 'nullable|string|max:10',
                'building_height' => 'nullable|string|max:10',
                'building_size' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            $property_video = null;

            if (isset($request->property_video)) {

                /** if video upload selected */

                if ($request->hasFile('property_video')) {

                    $request->validate([
                        'property_video' => 'mimes:mp4,webm,flv,wmv,3gp,mov|max:20480',
                    ], [
                        'property_video.max' => 'Property video may not be greater then 20MB',
                    ]);

                    $filename = public_path() . '/new_images/' . $property->property_video;
                    \File::delete($filename);

                    $property_video = time() . '.' . $request->property_video->getClientOriginalExtension();
                    $request->property_video->move(public_path('new_images'), $property_video);

                }

                /** If url selected */

                if (!$request->hasFile('property_video') && $request->property_video) {
                    $property_video = $request->property_video;
                }

            }

            $post['property_video'] = $property_video;

            if ($request->file('thumbnail')) {
                $filename = public_path() . '/new_images/' . $property->thumbnail_image;
                // $filename = public_path('/new_images/'.$property->thumbnail_image);
                \File::delete($filename);

                $imageName = time() . '.' . $request->thumbnail->getClientOriginalExtension();

                $request->thumbnail->move(public_path('new_images'), $imageName);
                $post['thumbnail_image'] = $imageName;
            }
            if ($request->file('manager_image')) {
                $filename = public_path() . '/new_images/' . $property->manager_image;
                // $filename = public_path('/new_images/'.$property->thumbnail_image);
                \File::delete($filename);

                $imageName = time() . '.' . $request->manager_image->getClientOriginalExtension();

                $request->manager_image->move(public_path('new_images'), $imageName);
                $post['manager_image'] = $imageName;
            }
            if ($request->file('conditionFile')) {
                $filename = public_path() . '/terms_and_condition/' . $property->terms_and_condition_file;
                \File::delete($filename);

                $file = time() . '.' . $request->conditionFile->getClientOriginalExtension();

                $request->conditionFile->move(public_path('terms_and_condition'), $file);
                $post['terms_and_condition_file'] = $file;
            }

            unset($post['thumbnail']);
            unset($post['attachments']);
            unset($post['conditionFile']);
            unset($post['selectedAmenity']);
            unset($post['selectedContract']);
            unset($post['layoutDesigns']);
            unset($post['nearByAmenities']);
            unset($post['additional_options']);
            $post['available_from'] = date("Y-m-d", strtotime($post['available_from']));

            $post['max_price'] = $post['max_price'];
            $post['min_price'] = $post['min_price'];
            unset($post['maximum_price']);
            unset($post['minimum_price']);

            $post['min_length'] = $post['min_length'];
            $post['max_length'] = $post['max_length'];

            $post['contract_type'] = $post['contract_type'];
            $post['max_contract_length_type'] = $post['max_contract_length_type'];
            $post['building_height'] = $post['building_height'];
            $post['building_size'] = $post['building_size'];
            $post['available_end'] = isset($post['available_end']) && $post['available_end'] != '' && $post['available_end'] != 'null' ? date("Y-m-d", strtotime($post['available_end'])) : null;


            $property->update($post);
            // amenities store
            PropertiesAmenities::where('property_id', $property->id)->delete();
            $amenities = json_decode($request->selectedAmenity);
            foreach ($amenities as $key => $value) {
                $propertyAmenities = PropertiesAmenities::create(
                    [
                        'property_id' => $property->id,
                        'amenity_id' => $value->id,
                        'created_at' => now(),
                    ]
                );
            }

            // contract length store
            PropertyContractLengths::where('property_id', $property->id)->delete();
            $contracts = json_decode($request->selectedContract);
            foreach ($contracts as $key => $value) {
                $propertyContracts = PropertyContractLengths::create(
                    [
                        'property_id' => $property->id,
                        'contract_length_id' => $value->id,
                        'percent' => $value->percent ?? 0,
                        'is_default' => $value->is_default ?? 0,
                        'created_at' => now(),
                    ]
                );
            }

            // layout design store
            PropertiesLayoutDesigns::where('property_id', $property->id)->delete();
            $layoutDesign = json_decode($request->layoutDesigns);
            foreach ($layoutDesign as $key => $value) {
                $propertyLayoutDesign = PropertiesLayoutDesigns::create(
                    [
                        'property_id' => $property->id,
                        'layout_design_id' => $value->id,
                        'price' => $value->price ?? 0,
                        'capacity' => $value->capacity,
                        'is_default' => $value->is_default ?? 0,
                        'created_at' => now(),
                    ]
                );
            }

            // near by amenities store
            PropertiesNearByAmenities::where('property_id', $property->id)->delete();
            if (isset($request->nearByAmenities)) {
                $nearByAmenities = json_decode($request->nearByAmenities);
                foreach ($nearByAmenities as $key => $value) {
                    $propertyNearByAmenities = PropertiesNearByAmenities::create(
                        [
                            'property_id' => $property->id,
                            'near_by_amenity_id' => $value->id,
                            'distance' => $value->dist,
                            'created_at' => now(),
                        ]
                    );
                }
            }

            // additonal options store
            PropertiesAdditionalOptions::where('property_id', $property->id)->delete();
            if (isset($request->additional_options)) {
                $additional_options = json_decode($request->additional_options);
                foreach ($additional_options as $key => $value) {
                    $additional_options = PropertiesAdditionalOptions::create(
                        [
                            'property_id' => $property->id,
                            'additional_option_id' => $value->id,
                            'price' => $value->price ?? 0,
                            'created_at' => now(),
                        ]
                    );
                }
            }

            // for multiple images upload
            if ($request->file('attachments')) {
                // PropertiesImages::where('property_id', $property->id)->delete();
                $files = $request->file('attachments');
                foreach ($files as $key => $value) {
                    $image = time() . '.' . $value->getClientOriginalName();

                    $value->move(public_path('new_images'), $image);

                    $propertyImage = PropertiesImages::create(
                        [
                            'property_id' => $property->id,
                            'image_name' => $image,
                            'created_at' => now(),
                        ]
                    );
                }
            }

            $property->thumbnail_image = "/new_images/" . $property->thumbnail_image;
            // $property->thumbnail_image =  public_path('/new_images/'.$property->thumbnail_image);
            $property->terms_and_condition_file = "/terms_and_condition/" . $property->terms_and_condition_file;

            return $this->sendResponse($property, 'Property updated successfully');
        } else {
            return $this->sendError('Error', 'Property does not exist', 404);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $property = Properties::find($id);
        if (!$property) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $contracts = Contracts::where('property_id', $property->id)->get();
            // if (count($contracts) > 0) {
            // PropertyContractLengths::where('property_id', $property->id)->delete();
            // PropertiesAmenities::where('property_id', $property->id)->delete();
            // PropertiesNearByAmenities::where('property_id', $property->id)->delete();
            // PropertiesAdditionalOptions::where('property_id', $property->id)->delete();
            // PropertiesImages::where('property_id', $property->id)->delete();
            // Contracts::where('property_id', $property->id)->delete();

            //     $properties = Properties::with('contract_lengths')->with('amenities')->paginate(5);
            //     return $this->sendResponse($properties, 'Property with contracts deleted successfully');
            //     // return $this->sendError('Error', 'This property multiple contracts, So you can not delete it!', 501);
            // } else {
            PropertyContractLengths::where('property_id', $property->id)->update(['soft_deleted' => 1]);
            PropertiesAmenities::where('property_id', $property->id)->update(['soft_deleted' => 1]);
            PropertiesNearByAmenities::where('property_id', $property->id)->update(['soft_deleted' => 1]);
            PropertiesAdditionalOptions::where('property_id', $property->id)->update(['soft_deleted' => 1]);
            PropertiesImages::where('property_id', $property->id)->update(['soft_deleted' => 1]);
            PropertiesLayoutDesigns::where('property_id', $property->id)->update(['soft_deleted' => 1]);

            $property->soft_deleted = 1;
            $property->delete();
            $property->save();
            // $property->delete();

            $properties = Properties::with('contract_lengths')->with('amenities')
                ->where('soft_deleted', 0)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return $this->sendResponse($properties, 'Property deleted successfully');
            // }
        }
        return $this->sendError('Error', 'Error in deletion', 500);
    }

    public function getUniqueAddresses(Request $request)
    {
        $excludedAddresses = [
            "Short Springs Drive, Austin, Texas, USA",
            "Canberra Drive, Singapore",
            "9019 River Rd, Huron, Ohio, USA",
            "Evergreen Lane, Cerritos, Los Angeles, CA, USA"
        ];

        // Fetch all properties with soft_deleted = 0 and is_available = 1
        $locations = Properties::where('soft_deleted', 0)
            ->where('is_available', 1)
            ->whereNotIn('location', $excludedAddresses)
            ->pluck('location') // Get only the 'location' field
            ->filter()         // Remove any null or empty locations
            ->unique()         // Ensure locations are unique
            ->values();        // Reindex the array keys

        // Initialize an empty array to hold the formatted unique addresses
        $uniqueAddresses = [];

        foreach ($locations as $location) {
            // Prepare the address for the Google Maps API
            $prepAddr = str_replace([' ', '#'], ['+', '%23'], $location);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU');
            $output = json_decode($geocode);

            if ($output->status == 'OK') {
                // Initialize variables for city, state, country
                $city = '';
                $state = '';
                $country = '';

                // Extract city, state, country from address components
                foreach ($output->results[0]->address_components as $component) {
                    if (in_array('locality', $component->types)) {
                        $city = $component->long_name;
                    }
                    if (in_array('administrative_area_level_1', $component->types)) {
                        $state = $component->short_name;
                    }
                    if (in_array('country', $component->types)) {
                        $country = $component->short_name;
                    }
                }

                // Fallback to ensure state and country are always included
                if (empty($city)) {
                    $city = ''; // No need to add "Unknown City" if you just want state, country
                }

                // Construct the formatted address
                $formattedAddress = trim($city . ', ' . $state . ', ' . $country, ', ');

                // Add the formatted address to the array if it's not already present
                if (!in_array($formattedAddress, $uniqueAddresses)) {
                    $uniqueAddresses[] = $formattedAddress;
                }
            }
        }

        return $this->sendResponse($uniqueAddresses, 'Unique city, state, and country addresses fetched successfully');
    }


    // public function getPropertyBySearch(Request $request)
    // {

    //     $post = $request->all();
    //     $location = "";
    //     $minCapacity = 0;
    //     $maxCapacity = 0;
    //     $minDate = "";
    //     $maxDate = "";

    //     $near_by_place = array();
    //     if (isset($post['location'])) {
    //         $location = explode(',', $post['location']);

    //         $address = $post['location']; // Google HQ
    //         $prepAddr = str_replace(' ', '+', $address);
    //         $prepAddr = str_replace('#', '%23', $prepAddr);
    //         $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU');
    //         $output = json_decode($geocode);

    //         if ($output->status == 'OK') {
    //             $shortStateName = $output->results[0]->address_components[0]->short_name;
    //             $longStateName = $output->results[0]->address_components[0]->long_name;
    //         }

    //         $latitude = round($output->results[0]->geometry->location->lat, 2);
    //         $longitude = round($output->results[0]->geometry->location->lng, 2);

    //         if(end($location)== $location[0]){

    //             $near_by_place = DB::table("properties")
    //             ->select("properties.*")
    //             ->where('properties.country', 'LIKE', '%' . $location[0] . '%')
    //             ->pluck('id')
    //             ->toArray();



    //         }else{

    //             $near_by_place = DB::table("properties")->select(
    //                 "properties.*",
    //                 DB::raw("ROUND((6371 *acos(cos(radians(" . $latitude . ")) *
    //                         cos(radians(properties.latitude)) *
    //                         cos(radians(properties.longitude) - radians(" . $longitude . ")) + sin(radians(" . $latitude . "))
    //                         * sin(radians(properties.latitude))
    //                         )),2) AS distance")
    //             )
    //                 ->having('distance', '<=', 15)
    //                 ->orderBy('distance')
    //                 ->pluck('id')->toArray();

    //         }
    //     }



    //     if (isset($post['max_capacity']) && isset($post['min_capacity'])) {

    //         $minCapacity = (int) $post['min_capacity'];
    //         $maxCapacity = (int) $post['max_capacity'];
    //     }

    //     if (isset($post['start_date'])) {
    //         $minDate = date('Y-m-d', strtotime($post['start_date'] . ' - 40 days')); //Carbon::parse($post['start_date'])->subDays(40)->format('Y-m-d');
    //         $maxDate = date('Y-m-d', strtotime($post['start_date'] . ' + 7 days')); //Carbon::parse($post['start_date'])->addDays(7)->format('Y-m-d');
    //     }

    //     $properties = Properties::where('is_available', 1);

    //     if (!empty($location)) {
    //         if (count($near_by_place) > 0) {
    //             $properties = Properties::where('is_available', 1)->Where(function ($q) use ($longitude, $latitude) {
    //                 $q->where('latitude', $latitude)->where('longitude', $longitude);
    //             })->orWhere(function ($q) use ($near_by_place) {
    //                 $q->WhereIn('id', $near_by_place);
    //             });
    //         } else {
    //             $properties->where('latitude', $latitude)->where('longitude', $longitude);
    //         }

    //     }


    //     if (!empty($minCapacity) && !empty($maxCapacity)) {
    //         $properties->whereBetween('capacity', [$minCapacity, $maxCapacity]);
    //     }
    //     if (!empty($minDate) && !empty($maxDate)) {

    //         $properties->whereBetween('available_from', ['2022-04-14', '2022-05-03']);
    //         //print_r($properties);
    //         //        print_r($properties->get());
    //     }
    //     // $properties = $properties->get();

    //     $properties = Properties::query();

    //     $properties = $properties->whereHas('contract_lengths')->having('is_available', 1)->having('soft_deleted', 0);

    //     if (!empty($location)) {
    //         if (count($near_by_place) > 0) {
    //             $properties->where(function ($q) use ($longitude, $latitude, $near_by_place, $shortStateName, $longStateName) {
    //                 $q->where('latitude', $latitude)->where('longitude', $longitude);
    //                 $q->orWhere(function ($r) use ($near_by_place) {
    //                     $r->WhereIn('id', $near_by_place);
    //                 });

    //                 $q->orwhere('state', 'LIKE', '%' . $shortStateName . '%')
    //                     ->orwhere('state', 'LIKE', '%' . $longStateName . '%');
    //             });
    //         } else {
    //             $properties
    //                 ->where('latitude', $latitude)
    //                 ->where('longitude', $longitude)
    //                 ->orwhere('state', 'LIKE', '%' . $shortStateName . '%')
    //                 ->orwhere('state', 'LIKE', '%' . $longStateName . '%');
    //         }
    //     }

    //     if (!empty($minCapacity) && !empty($maxCapacity)) {

    //         $properties->whereHas('layoutDesigns', function ($query) use ($minCapacity, $maxCapacity) {
    //             return $query->whereBetween('capacity', [$minCapacity, $maxCapacity]);
    //         });

    //     }

    //     if (!empty($minDate) && !empty($maxDate)) {
    //         $properties->whereBetween('available_from', [$minDate, $maxDate]);
    //     }

    //     $properties = $properties->get();

    //     foreach ($properties as $key => $value) {
    //         $distance = $this->getDistanceBetweenPointsNew($latitude, $longitude, $value->latitude, $value->longitude);
    //         $properties[$key]['distance'] = $distance;
    //         $images = PropertiesImages::where('property_id', $value->id)->where('soft_deleted', 0)->get();
    //         $imageCount = $images->count();
    //         $properties[$key]['imageCount'] = $imageCount;

    //         $contractLength = PropertyContractLengths::with('contract_length')->where('property_id', $value->id)->where('soft_deleted', 0)->get();
    //         $properties[$key]['contractLengths'] = $contractLength;

    //         $layoutDesigns = PropertiesLayoutDesigns::with('layoutDesign')->where('property_id', $value->id)->where('soft_deleted', 0)->get();
    //         $properties[$key]['layoutDesigns'] = $layoutDesigns;
    //     }

    //     $properties = $properties->sortBy('distance');

    //     if ($properties->count() == 0) {

    //         $minCapacity = 0;
    //         $maxCapacity = 0;
    //         $minDate = "";
    //         $maxDate = "";
    //         $location = "";

    //         if (isset($post['location'])) {
    //             $location = explode(',', $post['location']);
    //         }

    //         if (isset($post['max_capacity']) && isset($post['min_capacity'])) {
    //             $minCapacity = $post['min_capacity'];
    //             $maxCapacity = $post['max_capacity'];
    //         }

    //         if (isset($post['start_date'])) {

    //             $minDate = Carbon::parse($post['start_date']);
    //             $maxDate = Carbon::parse($post['start_date'])->addDays(30);
    //         }

    //         $capacityProperties = Properties::where('is_available', 1)->where('soft_deleted', 0);

    //         if (!empty($minCapacity)) {
    //             $capacityProperties->where('capacity', $minCapacity);
    //         }

    //         if (!empty($location)) {
    //             $capacityProperties->where('latitude', $latitude)->where('longitude', $longitude);
    //         }

    //         if (!empty($maxDate)) {
    //             $capacityProperties->where('available_from', $maxDate);
    //         }

    //         $capacityProperties = $capacityProperties->get();

    //         $dateProperties = Properties::where('is_available', 1)->where('soft_deleted', 0);
    //         if (!empty($maxDate)) {
    //             $dateProperties->where('available_from', $maxDate);
    //         }

    //         if (!empty($minCapacity)) {
    //             $dateProperties->where('capacity', $minCapacity);
    //         }

    //         if (!empty($location)) {
    //             $dateProperties->where('latitude', $latitude)->where('longitude', $longitude);
    //         }

    //         $dateProperties = $dateProperties->get();

    //         $data['capacityProperties'] = count($capacityProperties);
    //         $data['dateProperties'] = count($dateProperties);
    //         $data['dateRange'] = $maxDate;
    //         $data['capacityRange'] = $minCapacity . "-" . $maxCapacity;

    //         return $this->sendResponse($data, 'Property fetched successfully');
    //     }

    //     Log::debug($properties->values());

    //     return $this->sendResponse($properties->values(), 'Property fetched successfully');
    // }

    // =========================================================
        // with all filteres
//     public function getPropertyBySearch(Request $request)
// {
//     $post = $request->all();
//     $latitude = $longitude = null;
//     $minCapacity = $maxCapacity = 0;
//     $minLeaseRate = $maxLeaseRate = 0;
//     $minAvailableSpace = $maxAvailableSpace = 0;
//     $minDate = $maxDate = "";

//     $near_by_place = [];
//     $shortStateName = $longStateName = '';

//     if (isset($post['location'])) {
//         $location = explode(',', $post['location']);
//         $address = $post['location'];
//         $prepAddr = str_replace([' ', '#'], ['+', '%23'], $address);
//         $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU');
//         $output = json_decode($geocode);

//         if ($output->status == 'OK') {
//             $shortStateName = $output->results[0]->address_components[0]->short_name;
//             $longStateName = $output->results[0]->address_components[0]->long_name;
//             $latitude = round($output->results[0]->geometry->location->lat, 2);
//             $longitude = round($output->results[0]->geometry->location->lng, 2);
//         }

//         if (end($location) == $location[0]) {
//             $near_by_place = DB::table("properties")
//                 ->where('country', 'LIKE', '%' . $location[0] . '%')
//                 ->pluck('id')
//                 ->toArray();
//         } else {
//             $near_by_place = DB::table("properties")
//                 ->select("id")
//                 ->selectRaw("ROUND((6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))), 2) AS distance", [$latitude, $longitude, $latitude])
//                 ->having('distance', '<=', 15)
//                 ->pluck('id')
//                 ->toArray();
//         }
//     }

//     if (isset($post['min_capacity']) && isset($post['max_capacity'])) {
//         $minCapacity = (int) $post['min_capacity'];
//         $maxCapacity = (int) $post['max_capacity'];
//     }

//     if (isset($post['min_lease_rate']) && isset($post['max_lease_rate'])) {
//         $minLeaseRate = (int) $post['min_lease_rate'];
//         $maxLeaseRate = (int) $post['max_lease_rate'];
//     }

//     if (isset($post['min_available_space']) && isset($post['max_available_space'])) {
//         $minAvailableSpace = (int) $post['min_available_space'];
//         $maxAvailableSpace = (int) $post['max_available_space'];
//     }

//     if (isset($post['start_date'])) {
//         $minDate = date('Y-m-d', strtotime(substr($post['start_date'], 0, 10)));
//     }

//     if (isset($post['end_date'])) {
//         $maxDate = date('Y-m-d', strtotime(substr($post['end_date'], 0, 10)));
//     }

//     $propertiesQuery = Properties::query()
//         ->where('is_available', 1)
//         ->whereNull('deleted_at');

//     if (!empty($latitude) && !empty($longitude)) {
//         if (count($near_by_place) > 0) {
//             $propertiesQuery->where(function ($q) use ($latitude, $longitude, $near_by_place, $shortStateName, $longStateName) {
//                 $q->where(function ($q) use ($latitude, $longitude) {
//                     $q->where('latitude', $latitude)->where('longitude', $longitude);
//                 })
//                 ->orWhereIn('id', $near_by_place)
//                 ->orWhere('state', 'LIKE', '%' . $shortStateName . '%')
//                 ->orWhere('state', 'LIKE', '%' . $longStateName . '%');
//             });
//         } else {
//             $propertiesQuery->where(function ($q) use ($latitude, $longitude, $shortStateName, $longStateName) {
//                 $q->where('latitude', $latitude)->where('longitude', $longitude)
//                   ->orWhere('state', 'LIKE', '%' . $shortStateName . '%')
//                   ->orWhere('state', 'LIKE', '%' . $longStateName . '%');
//             });
//         }
//     }

//     if ($minCapacity > 0 && $maxCapacity > 0) {
//         $propertiesQuery->whereBetween('capacity', [$minCapacity, $maxCapacity]);
//     }

//     if ($minLeaseRate > 0 && $maxLeaseRate > 0) {
//         $propertiesQuery->whereBetween('lease_rate', [$minLeaseRate, $maxLeaseRate]);
//     }

//     if ($minAvailableSpace > 0 && $maxAvailableSpace > 0) {
//         $propertiesQuery->whereBetween('available_space', [$minAvailableSpace, $maxAvailableSpace]);
//     }

//     if (!empty($minDate) && !empty($maxDate)) {
//         $propertiesQuery->whereBetween('available_from', [$minDate, $maxDate]);
//     } elseif (!empty($minDate)) {
//         $propertiesQuery->where('available_from', '>=', $minDate);
//     }

//     if (isset($post['workspace_category'])) {
//         $propertiesQuery->where('workspace_category', 'LIKE', '%' . $post['workspace_category'] . '%');
//     }

//     $properties = $propertiesQuery->get();

//     foreach ($properties as $key => $property) {
//         $distance = $this->getDistanceBetweenPointsNew($latitude, $longitude, $property->latitude, $property->longitude);
//         $property->distance = $distance;

//         $images = PropertiesImages::where('property_id', $property->id)->where('soft_deleted', 0)->get();
//         $property->imageCount = $images->count();

//         $contractLength = PropertyContractLengths::with('contract_length')->where('property_id', $property->id)->where('soft_deleted', 0)->get();
//         $property->contractLengths = $contractLength;

//         $layoutDesigns = PropertiesLayoutDesigns::with('layoutDesign')->where('property_id', $property->id)->where('soft_deleted', 0)->get();
//         $property->layoutDesigns = $layoutDesigns;
//     }

//     $properties = $properties->sortBy('distance');

//     if ($properties->isEmpty()) {
//         $data = [
//             'capacityProperties' => Properties::where('is_available', 1)
//                 ->where('soft_deleted', 0)
//                 ->whereBetween('capacity', [$minCapacity, $maxCapacity])
//                 ->count(),
//             'leaseRateProperties' => Properties::where('is_available', 1)
//                 ->where('soft_deleted', 0)
//                 ->whereBetween('lease_rate', [$minLeaseRate, $maxLeaseRate])
//                 ->count(),
//             'availableSpaceProperties' => Properties::where('is_available', 1)
//                 ->where('soft_deleted', 0)
//                 ->whereBetween('available_space', [$minAvailableSpace, $maxAvailableSpace])
//                 ->count(),
//             'dateProperties' => Properties::where('is_available', 1)
//                 ->where('soft_deleted', 0)
//                 ->whereBetween('available_from', [$minDate, $maxDate])
//                 ->count(),
//             'dateRange' => $maxDate,
//             'capacityRange' => $minCapacity . "-" . $maxCapacity,
//         ];

//         return $this->sendResponse($data, 'Property fetched successfully in empty case');
//     }

//     return $this->sendResponse($properties->values(), 'Property fetched successfully');
// }





    // =========================================================

    public function getPropertyBySearch(Request $request)
    {
        $post = $request->all();
        $latitude = $longitude = null;
        $minCapacity = $maxCapacity = 0;
        $minContractLength = "";
        $minContractLengthType = 'months';

        // Define minDate and maxDate
        $minDate = $maxDate = "";

        $near_by_place = [];
        $shortStateName = $longStateName = '';
        $buildingClass = $post['building_class'] ?? null;
        $floorSize = $post['floor_size'] && $post['floor_size'] > 0 ? $post['floor_size'] : null;

        if (isset($post['location'])) {
            $location = explode(',', $post['location']);
            $address = $post['location'];
            $prepAddr = str_replace([' ', '#'], ['+', '%23'], $address);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU');
            $output = json_decode($geocode);

            if ($output->status == 'OK') {
                $shortStateName = $output->results[0]->address_components[0]->short_name;
                $longStateName = $output->results[0]->address_components[0]->long_name;
                $latitude = round($output->results[0]->geometry->location->lat, 2);
                $longitude = round($output->results[0]->geometry->location->lng, 2);
            }

            if (end($location) == $location[0]) {
                $near_by_place = DB::table("properties")
                    ->where('country', 'LIKE', '%' . $location[0] . '%')
                    ->pluck('id')
                    ->toArray();
            } else {
                $near_by_place = DB::table("properties")
                    ->select("id")
                    ->selectRaw("ROUND((6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))), 2) AS distance", [$latitude, $longitude, $latitude])
                    ->having('distance', '<=', 15)
                    ->pluck('id')
                    ->toArray();
            }
        }

        if (isset($post['min_capacity']) && isset($post['max_capacity'])) {
            $minCapacity = (int) $post['min_capacity'];
            $maxCapacity = (int) $post['max_capacity'];
        }

        // Process the start_date
        if (isset($post['start_date'])) {
            // Convert start_date to Y-m-d format
            $minDate = date('Y-m-d', strtotime($post['start_date']));
        }

        if(isset($post['min_contract_length']) ){
            $minContractLength = (int) $post['min_contract_length'];
        }

        if(isset($post['min_contract_length_type']) ){
            $minContractLengthType = $post['min_contract_length_type'];
        }

        $propertiesQuery = Properties::query()
            ->where('is_available', 1)
            ->whereNull('deleted_at');

        if (!empty($latitude) && !empty($longitude)) {
            if (count($near_by_place) > 0) {
                $propertiesQuery->where(function ($q) use ($latitude, $longitude, $near_by_place, $shortStateName, $longStateName) {
                    $q->where(function ($q) use ($latitude, $longitude) {
                        $q->where('latitude', $latitude)->where('longitude', $longitude);
                    })
                    ->orWhereIn('id', $near_by_place)
                    ->orWhere('state', 'LIKE', '%' . $shortStateName . '%')
                    ->orWhere('state', 'LIKE', '%' . $longStateName . '%');
                });
            } else {
                $propertiesQuery->where(function ($q) use ($latitude, $longitude, $shortStateName, $longStateName) {
                    $q->where('latitude', $latitude)->where('longitude', $longitude)
                      ->orWhere('state', 'LIKE', '%' . $shortStateName . '%')
                      ->orWhere('state', 'LIKE', '%' . $longStateName . '%');
                });
            }
        }

        if ($minCapacity > 0 && $maxCapacity > 0) {
            $propertiesQuery->whereBetween('capacity', [$minCapacity, $maxCapacity]);
        }


        if (!empty($minDate)) {
            $propertiesQuery->where(function($query) use ($minDate) {
                $query->whereNull('available_end')
                        ->orWhere('available_end', '>=', $minDate);
            });
        }


        if (!empty($minContractLength) && $minContractLength > 0) {
            $propertiesQuery->where('min_length', '>=', $minContractLength);

            if (!empty($minContractLengthType)) {
                $propertiesQuery->where('contract_type', $minContractLengthType);
            }
        }

        if (!empty($buildingClass) && is_array($buildingClass) && count($buildingClass) > 0) {
            $propertiesQuery->whereIn('property_class', $buildingClass);
        }

        if (!empty($floorSize) && $floorSize > 0) {
            $propertiesQuery->where('area', '>=', $floorSize);
        }

        $properties = $propertiesQuery->get();

        foreach ($properties as $key => $property) {
            $distance = $this->getDistanceBetweenPointsNew($latitude, $longitude, $property->latitude, $property->longitude);
            $property->distance = $distance;

            $images = PropertiesImages::where('property_id', $property->id)->where('soft_deleted', 0)->get();
            $property->imageCount = $images->count();

            $contractLength = PropertyContractLengths::with('contract_length')->where('property_id', $property->id)->where('soft_deleted', 0)->get();
            $property->contractLengths = $contractLength;

            $layoutDesigns = PropertiesLayoutDesigns::with('layoutDesign')->where('property_id', $property->id)->where('soft_deleted', 0)->get();
            $property->layoutDesigns = $layoutDesigns;
        }

        $properties = $properties->sortBy('distance');

        if ($properties->isEmpty()) {
            $data = [
                'capacityProperties' => Properties::where('is_available', 1)
                    ->where('soft_deleted', 0)
                    ->whereBetween('capacity', [$minCapacity, $maxCapacity])
                    ->count(),
                'dateProperties' => Properties::where('is_available', 1)
                    ->where('soft_deleted', 0)
                    ->where('available_from', '>=', $minDate)
                    ->count(),
                'dateRange' => $minDate,
                'capacityRange' => $minCapacity . "-" . $maxCapacity,
            ];

            return $this->sendResponse($data, 'Property fetched successfully in empty case');
        }

        return $this->sendResponse($properties->values(), 'Property fetched successfully');
    }

    // =========================================================


    // public function getPropertyBySearch(Request $request)
    //     {
    //         $post = $request->all();
    //         $latitude = $longitude = null;
    //         $minCapacity = $maxCapacity = 0;

    //         $minDate = $maxDate = "";

    //         $near_by_place = [];
    //         $shortStateName = $longStateName = '';

    //         if (isset($post['location'])) {
    //             $location = explode(',', $post['location']);
    //             $address = $post['location'];
    //             $prepAddr = str_replace([' ', '#'], ['+', '%23'], $address);
    //             $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU');
    //             $output = json_decode($geocode);

    //             if ($output->status == 'OK') {
    //                 $shortStateName = $output->results[0]->address_components[0]->short_name;
    //                 $longStateName = $output->results[0]->address_components[0]->long_name;
    //                 $latitude = round($output->results[0]->geometry->location->lat, 2);
    //                 $longitude = round($output->results[0]->geometry->location->lng, 2);
    //             }

    //             if (end($location) == $location[0]) {
    //                 $near_by_place = DB::table("properties")
    //                     ->where('country', 'LIKE', '%' . $location[0] . '%')
    //                     ->pluck('id')
    //                     ->toArray();
    //             } else {
    //                 $near_by_place = DB::table("properties")
    //                     ->select("id")
    //                     ->selectRaw("ROUND((6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))), 2) AS distance", [$latitude, $longitude, $latitude])
    //                     ->having('distance', '<=', 15)
    //                     ->pluck('id')
    //                     ->toArray();
    //             }
    //         }

    //         if (isset($post['min_capacity']) && isset($post['max_capacity'])) {
    //             $minCapacity = (int) $post['min_capacity'];
    //             $maxCapacity = (int) $post['max_capacity'];
    //         }

    //         if (isset($post['start_date'])) {
    //             $minDate = date('Y-m-d', strtotime($post['start_date'] . ' - 40 days'));
    //             $maxDate = date('Y-m-d', strtotime($post['start_date'] . ' + 7 days'));
    //         }

    //         $propertiesQuery = Properties::query()
    //             ->where('is_available', 1)
    //             ->whereNull('deleted_at');

    //         if (!empty($latitude) && !empty($longitude)) {
    //             if (count($near_by_place) > 0) {
    //                 $propertiesQuery->where(function ($q) use ($latitude, $longitude, $near_by_place, $shortStateName, $longStateName) {
    //                     $q->where(function ($q) use ($latitude, $longitude) {
    //                         $q->where('latitude', $latitude)->where('longitude', $longitude);
    //                     })
    //                     ->orWhereIn('id', $near_by_place)
    //                     ->orWhere('state', 'LIKE', '%' . $shortStateName . '%')
    //                     ->orWhere('state', 'LIKE', '%' . $longStateName . '%');
    //                 });
    //             } else {
    //                 $propertiesQuery->where(function ($q) use ($latitude, $longitude, $shortStateName, $longStateName) {
    //                     $q->where('latitude', $latitude)->where('longitude', $longitude)->orWhere('state', 'LIKE', '%' . $shortStateName . '%')->orWhere('state', 'LIKE', '%' . $longStateName . '%');
    //                 });
    //             }
    //         }

    //         if ($minCapacity > 0 && $maxCapacity > 0) {

    //             $propertiesQuery->whereBetween('capacity', [$minCapacity, $maxCapacity]);
    //             // return $this->sendResponse($propertiesQuery->get(), 'Property fetched successfully IF ELSE');
    //         }

    //         if (!empty($minDate) && !empty($maxDate)) {
    //             $propertiesQuery->whereBetween('available_from', [$minDate, $maxDate]);
    //         }

    //         $properties = $propertiesQuery->get();

    //         foreach ($properties as $key => $property) {
    //             $distance = $this->getDistanceBetweenPointsNew($latitude, $longitude, $property->latitude, $property->longitude);
    //             $property->distance = $distance;

    //             $images = PropertiesImages::where('property_id', $property->id)->where('soft_deleted', 0)->get();
    //             $property->imageCount = $images->count();

    //             $contractLength = PropertyContractLengths::with('contract_length')->where('property_id', $property->id)->where('soft_deleted', 0)->get();
    //             $property->contractLengths = $contractLength;

    //             $layoutDesigns = PropertiesLayoutDesigns::with('layoutDesign')->where('property_id', $property->id)->where('soft_deleted', 0)->get();
    //             $property->layoutDesigns = $layoutDesigns;
    //         }

    //         $properties = $properties->sortBy('distance');

    //         if ($properties->isEmpty()) {
    //             $data = [
    //                 'capacityProperties' => Properties::where('is_available', 1)
    //                     ->where('soft_deleted', 0)
    //                     ->whereBetween('capacity', [$minCapacity, $maxCapacity])
    //                     ->count(),
    //                 'dateProperties' => Properties::where('is_available', 1)
    //                     ->where('soft_deleted', 0)
    //                     ->whereBetween('available_from', [$minDate, $maxDate])
    //                     ->count(),
    //                 'dateRange' => $maxDate,
    //                 'capacityRange' => $minCapacity . "-" . $maxCapacity,
    //             ];

    //             return $this->sendResponse($data, 'Property fetched successfully in empty case');
    //         }

    //         return $this->sendResponse($properties->values(), 'Property fetched successfully');
    //     }

    // public function getPropertyBySearch(Request $request)
    //     {
    //         $post = $request->all();
    //         $latitude = $longitude = null;
    //         $minCapacity = $maxCapacity = 0;
    //         return $this->sendResponse($minCapacity, 'Property fetched successfully IF ELSE');
    //     }


    // ===================================================================

//     public function getPropertyBySearch(Request $request)
// {
//     // Get the input data
//     $post = $request->all();

//     // Dummy data to return in the response
//     $dummyData = [
//         [
//             'id' => 1,
//             'name' => 'Dummy Property 1',
//             'latitude' => 38.8951,
//             'longitude' => -77.0364,
//             'distance' => 0.5,
//             'imageCount' => 3,
//             'contractLengths' => [
//                 ['id' => 1, 'name' => 'Short Term'],
//                 ['id' => 2, 'name' => 'Long Term']
//             ],
//             'layoutDesigns' => [
//                 ['id' => 1, 'name' => 'Open Plan'],
//                 ['id' => 2, 'name' => 'Cubicles']
//             ]
//         ],
//         [
//             'id' => 2,
//             'name' => 'Dummy Property 2',
//             'latitude' => 38.8895,
//             'longitude' => -77.0353,
//             'distance' => 1.0,
//             'imageCount' => 5,
//             'contractLengths' => [
//                 ['id' => 3, 'name' => 'Monthly'],
//                 ['id' => 4, 'name' => 'Yearly']
//             ],
//             'layoutDesigns' => [
//                 ['id' => 3, 'name' => 'Private Offices'],
//                 ['id' => 4, 'name' => 'Shared Workspace']
//             ]
//         ]
//     ];

//     // Log the input data for debugging purposes
//     Log::debug('Input data:', $post);

//     // Return the dummy data in the response
//     return $this->sendResponse($dummyData, 'Property fetched successfully');
//     }


    public function deletePropertyImage($id)
    {
        $image = PropertiesImages::find($id);
        if (!$image) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $filename = public_path() . '/new_images/' . $image->image_name;
            // $filename = public_path('/new_images/'.$image->image_name);
            \File::delete($filename);

            $image->delete();
            return $this->sendResponse($image, 'Image deleted successfully');
        }
    }

    public function getLocationData(Request $request, $input)
    {

        $request->validate([
            'type' => 'required|numeric|in:1,0',
        ]);

        $client = new Client();
        $addData = "";

        if (($request->get('addData'))) {
            $addData = $request->get('addData');
        }

        $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';

        if ($addData) {
            $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json' . $addData;
        }

        $response = $client->get(
            $url,
            ['query' => ['types' => request()->type == 0 ? 'address' : '', 'key' => 'AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU', 'input' => $input]]
        );

        return ($response->getBody()->getContents());
    }

    public function getPlaceData(Request $request, $placeId)
    {
        $client = new Client();

        $url = 'https://maps.googleapis.com/maps/api/place/details/json';

        $response = $client->get(
            $url,
            ['query' => ['key' => 'AIzaSyDAsTPJuLMC7cZkbcnHnwgfK1HQgRe8LEU', 'place_id' => $placeId]]
        );

        return ($response->getBody()->getContents());
    }
    public function update_title(Request $request)
    {
        $post = $request->all();
        $validator = Validator::make($post, [
            'property_id' => 'required',
            'title' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 402);
        }

        $properties = Properties::find($post['property_id']);
        if (!empty($properties)) {
            if (Auth::user()->role == "admin") {
                $properties->title = $post['title'];
                $properties->save();
                return $this->sendResponse($properties, 'Title updated successfully');
            } else {
                return $this->sendError("You do not have permision to edit title", 402);
            }
        } else {
            return $this->sendError('Error', 'Property does not exist', 404);
        }
    }

    public function get_calculation($propertyId, $contractLengthId, PropertiesLayoutDesigns $propertyLayoutDesign)
    {
        // 2.44.7
        $price = $propertyLayoutDesign->price;
        $headCount = $propertyLayoutDesign->capacity; //HeadCount

        // Percentage value From PropertyContractLengths Model
        $percent = app(PropertyContractLengths::class)->getPercent($propertyId, $contractLengthId);

        // Calculation
        $perPerson = ((($price * $percent) / 100) / $headCount);
        $totalPrice = ($price * $percent) / 100;

        $calculation['perPerson'] = $perPerson ?? 0;
        $calculation['totalPrice'] = $totalPrice ?? 0;
        return $this->sendResponse($calculation, 'Calculation Result');
    }

    public function bulkDelete(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'property_ids.*' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 402);
        }

        Properties::findMany($request->property_ids)->each(function ($property) {

            DB::beginTransaction();

            /* Has many relation delete */

            $property->contract_lengths()->update([
                'soft_deleted' => '1',
            ]);

            /** Delete Images */

            $property->images()->each(function ($image) {
                try {

                    unlink(public_path("/new_images/{$image->image_name}"));

                } catch (\Exception $e) {

                }
            });

            /** Delete Thumb */

            try {

                unlink(public_path("/new_images/{$property->thumbnail_image}"));

            } catch (\Exception $e) {

            }

            /** Delete TOS FILES */

            try {

                unlink(public_path("/terms_and_condition/{$property->terms_and_condition_file}"));

            } catch (\Exception $e) {

            }

            $property->images()->update([
                'soft_deleted' => '1',
            ]);

            $property->nearByAmenities()->update([
                'soft_deleted' => '1',
            ]);
            $property->layoutDesigns()->update([
                'soft_deleted' => '1',
            ]);

            $property->additional_options()->update([
                'soft_deleted' => '1',
            ]);

            /** Belongs to many relation delete */

            $property->amenities()->update([
                'soft_deleted' => '1',
            ]);

            $property->property_contractLength()->update([
                'soft_deleted' => '1',
            ]);

            $property->delete();

            DB::commit();

        });

        return $this->sendResponse('', 'Selected properties has been deleted !');

    }

    public function bulkUpload(Request $request)
    {

        ini_set('max_execution_time', 3600);

        $validator = Validator::make(
            [
                'file' => $request->file,
                'extension' => strtolower($request->file->getClientOriginalExtension()),
            ],
            [
                'file' => 'required|max:2000',
                'extension' => 'required|in:csv,xlsx,xls',
            ], [
                'extension.in' => 'Invalid file ! only csv, xlsx, xls file supported !',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 402);
        }

        /** File upload code  */

        if (!is_dir(public_path('excel/'))) {
            mkdir(public_path('excel/'), 0755, true);
        }

        if (!file_exists(public_path('/excel/index.php'))) {
            $fileContent = '<?php echo "Access denied !"; ?>';
            file_put_contents(public_path('/excel/index.php'), $fileContent);
        }

        if ($request->hasFile('file')) {

            $filename = 'property_sheet_' . time() . '.' . $request->file->getClientOriginalExtension();

            $filetype = $request->file->getClientOriginalExtension();

            $request->file->move(public_path('excel/'), $filename);

            $properties = (new FastExcel)->import(public_path('/excel/' . $filename));

            if (count($properties) < 1) {
                return $this->sendError('Validation error', array('file' => ['File is empty']), 402);
            }

        }

        $rules = [
            '*.title' => 'required|string',
            '*.location' => 'required|string',
            '*.latitude' => 'sometimes|regex:/^-?\d*(\.\d{0,15})?$/',
            '*.longitude' => 'sometimes|regex:/^-?\d*(\.\d{0,15})?$/',
            '*.state' => 'required|string',
            '*.pincode' => 'required',
            '*.available_date' => 'required',
            '*.layout_design_uuid' => 'required|regex:/^[^,\s]+(.+[,])*(.+[^, ])$/i',
            '*.layout_design_cost' => 'required|regex:/^([0-9]+[,])*([0-9]+)$/i',
            '*.layout_design_headcount' => 'required|regex:/^([0-9]+[,])*([0-9]+)$/i',
            '*.contract_length_uuid' => 'required|regex:/^([a-z0-9+$]+[,])*([a-z0-9+$]+)$/i',
            '*.contract_length_percent' => 'required|regex:/^(([0-9]*[.])?[0-9]+[,])*(([0-9]*[.])?[0-9]+)$/i',
            '*.is_available' => 'required|in:1,0',
            '*.manager_name' => 'required|string',
            '*.manager_email' => 'required|email',
            '*.manager_phone_number' => 'required',
            '*.amenties' => 'sometimes|regex:/^[^,\s]+(.+[,])*(.+[^, ])$/i',
            '*.near_by_amenties' => 'sometimes|regex:/^[^,\s]+(.+[,])*(.+[^, ])$/i',
            '*.near_by_amenties_distance' => 'sometimes|regex:/^(([0-9]*[.])?[0-9]+[,])*(([0-9]*[.])?[0-9]+)$/i',
            '*.additional_option_uuid' => 'sometimes|regex:/^[^,\s]+(.+[,])*(.+[^, ])$/i',
            '*.additional_option_price' => 'sometimes|regex:/^([0-9]+[,])*([0-9]+)$/i',
            '*.area' => 'sometimes|numeric',
            '*.floors' => 'sometimes|numeric',
            '*.gallery_folder' => 'required|string',
            '*.thumbnail_folder' => 'required|string',
            '*.tos_file_folder' => 'required|string',
        ];

        $messages = [];

        foreach ($properties->toArray() as $rowNum => $row) {
            foreach ($row as $field => $value) {

                $messages["{$rowNum}." . $field . '.required'] = "At Row {$rowNum} : The field {$field} is required";
                $messages["{$rowNum}." . $field . '.email'] = "At Row {$rowNum} : The field {$field} should be valid email";
                $messages["{$rowNum}." . $field . '.in'] = "At Row {$rowNum} : The field {$field} value should be 1 or 0";
                $messages["{$rowNum}." . $field . '.string'] = "At Row {$rowNum} : The field {$field} value must be string";
                $messages["{$rowNum}." . $field . '.regex'] = "At Row {$rowNum} : The field {$field} format is invalid.";
                $messages["{$rowNum}." . $field . '.numeric'] = "At Row {$rowNum} : The field {$field} must be a number.";

            }
        }

        $validator = Validator::make($properties->toArray(), $rules, $messages)->validate();

        BulkUploadProperties::dispatch($properties, $filetype, auth()->id());

        try {

            unlink(public_path('/excel/' . $filename));

        } catch (\Exception $e) {

        }

        return $this->sendResponse('', __(':count properties queued for upload !', ['count' => $properties->count()]));

    }

    public function excelInstructions()
    {

        $instructions[] = array(
            'column_name' => 'title',
            'description' => 'Title of property should be string.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'location',
            'description' => 'Location of property should be string.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'latitude',
            'description' => 'Latitude of property should be like this eg 24.32894200.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'longitude',
            'description' => 'Longitude of property should be like this eg 74.32894200.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'state',
            'description' => 'State name of property should be string.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'pincode',
            'description' => 'Pincode of property should be numeric.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'area',
            'description' => 'Area of property should be numeric value.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'floors',
            'description' => 'Floors of property should be numeric value.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'available_date',
            'description' => 'Available date of property should be be like this Y-m-d or d-m-Y format.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'class_type',
            'description' => 'Property class type should be be string.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'description',
            'description' => 'Property description should be string.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'amenties',
            'description' => 'Amenties UUID should be separated by comma eg: ABC123, BCA415 and it should be obtained from Amenities section.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'near_by_amenties',
            'description' => 'Near by amenties UUID should be separated by comma eg: ABC123, BCA415 and it should be obtained from Near By Amenities section.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'near_by_amenties_distance',
            'description' => 'near_by_amenties_distance should be in numeric format and in same order as you ordered the near by amenities UUIDs.',
            'required' => 'Required if near_by_amenties given',
        );

        $instructions[] = array(
            'column_name' => 'layout_design_uuid',
            'description' => 'layout_design_uuid should be separated by comma eg: ABC123, BCA415 and it should be obtained from Layout Design section.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'layout_design_cost',
            'description' => 'layout_design_cost should be in numeric format and in same order as you ordered the layout_design_uuid so it will attach the cost value in same order to respective layout design.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'layout_design_headcount',
            'description' => 'layout_design_headcount should be in numeric format and in same order as you ordered the layout_design_uuid so it will attach the headcount value in same order to respective layout design.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'contract_length_uuid',
            'description' => 'contract_length_uuid should be separated by comma eg: ABC123, BCA415 and it should be obtained from Contract Lenghts section.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'contract_length_percent',
            'description' => 'contract_length_percent should be in same order as you ordered the contract_length_uuid so it will attach the contract length percent value to respective contract length.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'additional_option_uuid',
            'description' => 'additional_option_uuid should be separated by comma eg: ABC123, BCA415 and it should be obtained from Additional Options section.',
            'required' => 'No',
        );

        $instructions[] = array(
            'column_name' => 'additional_option_price',
            'description' => 'additional_option_price should be in same order as you ordered the additional_option_uuid so it will attach that additional option price value to respective additional_option.',
            'required' => 'Required if additional_option_uuid given',
        );

        $instructions[] = array(
            'column_name' => 'is_available',
            'description' => 'Property available or not status where 1 = available and 0 = Not available.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'manager_name',
            'description' => 'Name of property manager and it should be string.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'manager_email',
            'description' => 'Email of property manager and it should be valid email address.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'manager_phone_number',
            'description' => 'Phone no of property manager.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'gallery_folder',
            'description' => 'Google drive folder ID or folder sharable url where property images are stored.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'tos_file_folder',
            'description' => 'Google drive folder ID or folder sharable url where terms and conditons file is stored.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'thumbnail_folder',
            'description' => 'Google drive folder ID or folder sharable url where thumbnail image file is stored.',
            'required' => 'Yes',
        );

        $instructions[] = array(
            'column_name' => 'video_url',
            'description' => 'Property video url if any eg: Youtube, vimeo or any mp4 url.',
            'required' => 'No',
        );

        return response()->json($instructions);

    }

    public function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        return (round($distance, 2));
    }
}
