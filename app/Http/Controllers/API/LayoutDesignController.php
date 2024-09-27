<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Models\LayoutDesigns;
use App\Models\PropertiesLayoutDesigns;
use Illuminate\Http\Request;
use Validator;

class LayoutDesignController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->get('perPage') == "all") {
            $layout_designs = LayoutDesigns::all();
        } else {
            $layout_designs = LayoutDesigns::paginate($request->get('perPage'));
        }
        return $this->sendResponse($layout_designs->toArray(), 'Layout designs fetched successfully');
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
            'name' => 'required|string|unique:layout_designs,name,NULL,id,deleted_at,NULL',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $layoutDesigns = LayoutDesigns::paginate(5);

        $layoutDesigns = LayoutDesigns::create(
            [
                'uuid' => LayoutDesigns::createUuid(),
                'name' => $post['name'],
                'is_default' => isset($post['is_default']) ? $post['is_default'] : "0",
            ]
        );
        return $this->sendResponse($layoutDesigns, 'Layout Design created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $layoutDesigns = LayoutDesigns::findOrFail($id); //Get content category specified by id
        if ($layoutDesigns) {

            $post = $request->all();

            $validator = Validator::make($post, [
                'name' => "required|string|unique:layout_designs,name,{$layoutDesigns->id},id,deleted_at,NULL",
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            if($layoutDesigns->uuid == ''){
                $post['uuid'] = LayoutDesigns::createUuid();
            }

            $layoutDesigns->update($post);
            return $this->sendResponse($layoutDesigns, 'Layout Design updated successfully');

        } else {
            return $this->sendError('Error', 'Record not found', 404);
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
        $layoutDesigns = LayoutDesigns::find($id);
        if (!$layoutDesigns) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $layoutDesignsAll = PropertiesLayoutDesigns::where('layout_design_id', $id)->get();
            if (count($layoutDesignsAll) > 0) {
                return $this->sendError('Error', 'This layout design has multiple properties, So you can not delete it!', 501);
            } else {
                if ($layoutDesigns->delete()) {
                    $layoutDesigns = LayoutDesigns::paginate(5);
                    return $this->sendResponse($layoutDesigns, 'Layout design deleted successfully');
                } else {
                    return $this->sendError('Error', 'Error in deletion', 500);
                }
            }
        }
        return $this->sendError('Error', 'Error in deletion', 500);
    }
}
