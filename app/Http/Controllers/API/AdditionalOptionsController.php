<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Models\AdditionalOptions;
use App\Models\ContractsAdditionalOptions;
use App\Models\PropertiesAdditionalOptions;
use Illuminate\Http\Request;
use Validator;

class AdditionalOptionsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->get('perPage') == "all") {
            $additionalOptions = AdditionalOptions::all();
        } else {
            $additionalOptions = AdditionalOptions::paginate($request->get('perPage'));
        }

        return $this->sendResponse($additionalOptions->toArray(), 'Contract Lengths fetched successfully');
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
            'name'=>'required|string|unique:additional_options,name,NULL,id,deleted_at,NULL'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $additionalOptions = AdditionalOptions::create(
            [
                'uuid' => AdditionalOptions::createUuid(),
                'name' => $post['name'],
                'basic' => (isset($post['basic']) && $post['basic'] != null) ? $post['basic'] : null,
                'standard' => (isset($post['standard']) && $post['standard'] != null) ? $post['standard'] : null,
                'premium' => (isset($post['premium']) && $post['premium'] != null) ? $post['premium'] : null,
            ]
        );
        $additionalOptions = AdditionalOptions::paginate(5);
        return $this->sendResponse($additionalOptions, 'Additional options created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AdditionalOptions  $additionalOptions
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $AdditionalOptionData = AdditionalOptions::where('id', $id)->first();
        if (!$AdditionalOptionData) {
            return $this->sendError('Error', 'Record not found', 404);
        }
        return $this->sendResponse($AdditionalOptionData, 'Contract Length fetched successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AdditionalOptions  $additionalOptions
     * @return \Illuminate\Http\Response
     */
    public function edit(AdditionalOptions $additionalOptions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AdditionalOptions  $additionalOptions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $additionalOption = AdditionalOptions::findOrFail($id); //Get content category specified by id
        if ($additionalOption) {
            $post = $request->all();

            $validator = Validator::make($post, [
                'name'=> "required|string|unique:additional_options,name,{$additionalOption->id},id,deleted_at,NULL"
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            if($additionalOption->uuid == ''){
                $post['uuid'] = AdditionalOptions::createUuid();
            }

            $additionalOption->update($post);

            return $this->sendResponse($additionalOption, 'Additional Option updated successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AdditionalOptions  $additionalOptions
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $additionalOption = AdditionalOptions::find($id);
        if (!$additionalOption) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $contracts = ContractsAdditionalOptions::where('additional_options_id', $id)->get();
            $property = PropertiesAdditionalOptions::where('additional_option_id', $id)->get();
            if (count($contracts) > 0 || count($property) > 0) {
                return $this->sendError('Error', 'This additional option has multiple contracts OR properties, So you can not delete it!', 501);
            } else {
                if ($additionalOption->delete()) {
                    $additionalOption = AdditionalOptions::paginate(5);
                    return $this->sendResponse($additionalOption, 'Additional Option deleted successfully');
                } else {
                    return $this->sendError('Error', 'Error in deletion', 500);
                }
            }
        }
    }
}
