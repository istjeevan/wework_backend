<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ContractLengths;
use App\Models\PropertyContractLengths;
use App\Http\Controllers\BaseController;
use Validator;

class ContractLengthsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->get('perPage') == "all") {
            $contractLenghts = ContractLengths::all();
        } else {
            $contractLenghts = ContractLengths::paginate($request->get('perPage'));
        }

        return $this->sendResponse($contractLenghts->toArray(), 'Contract Lengths fetched successfully');
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
            'length' => 'required|unique:contract_lengths,length,NULL,id,deleted_at,NULL'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $allContractLength = ContractLengths::all();
        if(count($allContractLength) > 0) {
            foreach ($allContractLength as $key => $value) {
                if($value->length == $post['length']) {
                    return $this->sendError('Validation Error.', 'Contract length with this length already exist.');
                } else {
                    $contractLength = ContractLengths::create(
                        [
                            'uuid' => ContractLengths::createUuid(),
                            'length' => $post['length']
                        ]
                    );
                    $contractLength = ContractLengths::paginate(5);
        
                    
                    return $this->sendResponse($contractLength, 'Contract Length created successfully');
                }
            }

        } else {
            $contractLength = ContractLengths::create(
                [
                    'uuid' => ContractLengths::createUuid(),
                    'length' => $post['length']
                ]
            );
            $contractLength = ContractLengths::paginate(5);

            
            return $this->sendResponse($contractLength, 'Contract Length created successfully');
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
        $contractLengthData = ContractLengths::where('id', $id)->first();
        if($contractLengthData) {
            return $this->sendResponse($contractLengthData, 'Contract Length fetched successfully');
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
        $contractLength = ContractLengths::findOrFail($id); //Get content category specified by id
        if($contractLength) {
            $post = $request->all();
            
            $validator = Validator::make($post, [
                'length'=> "required|unique:contract_lengths,length,{$id},id,deleted_at,NULL"
            ]);
    
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            $allContractLength = ContractLengths::where('id', '!=', $id)->get();
            if(count($allContractLength) > 0) {
                foreach ($allContractLength as $key => $value) {
                    if($value->length == $post['length']) {
                        return $this->sendError('Validation Error.', 'Contract length with this length already exist.');
                    } else {

                        if($contractLength->uuid == ''){
                            $post['uuid'] = ContractLengths::createUuid();
                        }

                        $contractLength->update($post);
                
                        return $this->sendResponse($contractLength, 'Contract Length updated successfully');
                    }
                }

            } else {

                if($contractLength->uuid == ''){
                    $post['uuid'] = ContractLengths::createUuid();
                }
                
                $contractLength->update($post);
                
                return $this->sendResponse($contractLength, 'Contract Length updated successfully');
            }
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
        $contractLength = ContractLengths::find($id);
        if(!$contractLength) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $propContractLengths = PropertyContractLengths::where('contract_length_id', $id)->get();
            if(count($propContractLengths) > 0) {
                return $this->sendError('Error', 'This contract length has multiple properties, So you can not delete it!', 501);
            } else {
                if($contractLength->delete()) {
                    $contractLenghts = ContractLengths::paginate(5);
                    return $this->sendResponse($contractLenghts,'Contract Length deleted successfully');
                } else {
                    return $this->sendError('Error', 'Error in deletion', 500);
                }
            }
        }
    }
}
