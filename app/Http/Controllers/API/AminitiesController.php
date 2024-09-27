<?php

namespace App\Http\Controllers\API;

use App\Models\Amenities;
use App\Models\PropertiesAmenities;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Validator;

class AminitiesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->get('perPage') == "all") {
            $amenities = Amenities::all();
        } else {
            $amenities = Amenities::paginate($request->get('perPage'));
        }
        return $this->sendResponse($amenities->toArray(), 'Amenities fetched successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
            'name'=>'required|string|unique:amenities,name,NULL,id,deleted_at,NULL',
            'icon_name' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $amenity = Amenities::create(
            [
                'uuid' => Amenities::createUuid(),
                'name' => $post['name'],
                'icon_name' => $post['icon_name'],
            ]
        );
        $amenity = Amenities::paginate(5);
        return $this->sendResponse($amenity, 'Amenities created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Amenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $amenity = Amenities::where('id', $id)->first();
        if($amenity) {
            return $this->sendResponse($amenity, 'Amenity fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Amenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function edit(Amenities $amenities)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Amenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $amenity = Amenities::findOrFail($id); //Get content category specified by id
        if($amenity) {
            $post = $request->all();
            
            $validator = Validator::make($post, [
                'name'=> "required|string|unique:amenities,name,{$amenity->id},id,deleted_at,NULL"
            ]);
    
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }
            
            if($amenity->uuid == ''){
                $post['uuid'] = Amenities::createUuid();
            }
            
            $amenity->update($post);
    
            return $this->sendResponse($amenity, 'Aminity updated successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Amenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $amenity = Amenities::find($id);
        if(!$amenity) {
            return $this->sendError('Error', 'Record not found', 404);
        }
        else {
            $propAmenities = PropertiesAmenities::where('amenity_id', $id)->get();
            if(count($propAmenities) > 0) {
                return $this->sendError('Error', 'This amenities has multiple properties, So you can not delete it!', 501);
            } else {
                if($amenity->delete()) {
                    $amenity = Amenities::paginate(5);
                    return $this->sendResponse($amenity,'Aminity deleted successfully');
                } else {
                    return $this->sendError('Error', 'Error in deletion', 500);
                }
            }
        }
    }
}
