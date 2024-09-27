<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Models\NearByAmenities;
use App\Models\PropertiesNearByAmenities;
use Illuminate\Http\Request;
use Validator;

class NearByAmenitiesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->get('perPage') == "all") {
            $near_by_amenities = NearByAmenities::all();
        } else {
            $near_by_amenities = NearByAmenities::paginate($request->get('perPage'));
        }
        return $this->sendResponse($near_by_amenities->toArray(), 'Near by Amenities fetched successfully');
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
            'name' => 'required|string|max:100|unique:near_by_amenities,name,NULL,id,deleted_at,NULL',
            'icon_name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $near_by_amenities = NearByAmenities::create(
            [
                'uuid' => NearByAmenities::createUuid(),
                'name' => $post['name'],
                'icon_name' => $post['icon_name'],
            ]
        );

        $near_by_amenities = NearByAmenities::paginate(5);
        return $this->sendResponse($near_by_amenities, 'Near by Amenities created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\NearByAmenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $near_by_amenities = NearByAmenities::where('id', $id)->first();
        if ($near_by_amenities) {
            return $this->sendResponse($near_by_amenities, 'Near by Amenity fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\NearByAmenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function edit(NearByAmenities $near_by_amenities)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\NearByAmenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $near_by_amenities = NearByAmenities::findOrFail($id); //Get content category specified by id
        if ($near_by_amenities) {
            $post = $request->all();

            $validator = Validator::make($post, [
                'name' => "required|string|max:100|unique:near_by_amenities,name,{$near_by_amenities->id},id,deleted_at,NULL",
                'icon_name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            if($near_by_amenities->uuid == ''){
                $post['uuid'] = NearByAmenities::createUuid();
            }

            $near_by_amenities->update($post);

            return $this->sendResponse($near_by_amenities, 'Near by Aminity updated successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\NearByAmenities  $amenities
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $near_by_amenities = NearByAmenities::find($id);
        if (!$near_by_amenities) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $propNearByAmenities = PropertiesNearByAmenities::where('near_by_amenity_id', $id)->get();
            if (count($propNearByAmenities) > 0) {
                return $this->sendError('Error', 'This near by amenities has multiple properties, So you can not delete it!', 501);
            } else {
                if ($near_by_amenities->delete()) {
                    $near_by_amenities = NearByAmenities::paginate(5);
                    return $this->sendResponse($near_by_amenities, 'Near by Aminity deleted successfully');
                } else {
                    return $this->sendError('Error', 'Error in deletion', 500);
                }
            }
        }
        return $this->sendError('Error', 'Error in deletion', 500);
    }
}
