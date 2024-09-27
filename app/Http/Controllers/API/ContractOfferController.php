<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Mail\ContractStatus;
use App\Models\ContractOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Validator;

class ContractOfferController extends BaseController
{

    public function index(Request $request)
    {

        if ($request->get('perPage') == "all") {
            $contract_offer = ContractOffer::with('contract_details', 'user_details')->all();
        } else {
            $contract_offer = ContractOffer::with('contract_details', 'user_details')->paginate($request->get('perPage'));
        }

        return $this->sendResponse($contract_offer->toArray(), 'Contract Offer fetched successfully');
    }
    public function store(Request $request)
    {
        $post = $request->all();
        $validator = Validator::make($post, [
            'contract_id' => 'required',
            'user_id' => 'required',
            'offered_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'phone_no' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $contract_offer = ContractOffer::create(
            [
                'contract_id' => $post['contract_id'],
                'user_id' => $post['user_id'],
                'phone_no' => $post['phone_no'],
                'offered_price' => $post['offered_price'],
                'status' => 'pending',
            ]
        );

        $contract_offer = ContractOffer::find($contract_offer->id);
        $admin_users = User::where('role', 'admin')->get();
        if (!$admin_users->isEmpty()) {
            foreach ($admin_users as $user) {
                Mail::send('emails.new_contract_offer', $contract_offer->toArray(), function ($message) use ($request, $user) {
                    $message->to($user->email, $user->name)->subject('New Contract Offer');
                    $message->from("engineering@extraslice.com", "ExtraSlice");
                });
            }
        }

        return $this->sendResponse($contract_offer, 'Contract offer created successfully');
    }
    public function show_contract_offer($id)
    {
        $contract_offer = ContractOffer::where('id', $id)->first();
        if ($contract_offer) {
            return $this->sendResponse($contract_offer, 'Contract offer fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }
    public function update_offer_status(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'offer_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $contract_offer = ContractOffer::with(['user_details','contract_details.property_details'])->find($post['offer_id']);
        if (!empty($contract_offer)) {
            $contract_offer->status = $post['status'];
            $contract_offer->save();

            /** Send contract status to user mail*/

            try{
                Mail::to($contract_offer->user_details->email)->send(new ContractStatus($contract_offer));
            }catch(\Exception $e){
                Log::error('Failed to send contract offer email '.$e->getMessage());
            }

            return $this->sendResponse($contract_offer, 'Contract offer update successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }
}
