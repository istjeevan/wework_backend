<?php

namespace App\Http\Controllers\API;

use App\Models\LayoutDesigns;
use App\Models\PropertiesLayoutDesigns;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Validator;

class LayoutDesignsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->get('perPage') == "all") {
            $layout_designs = LayoutDesigns::all();
        } else {
            $layout_designs = LayoutDesigns::paginate($request->get('perPage'));
        }
        return $this->sendResponse($layout_designs->toArray(), 'Layout designs fetched successfully');
    }

}
