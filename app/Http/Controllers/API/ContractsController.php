<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Models\ContractOffer;
use App\Models\Contracts;
use App\Models\ContractsAdditionalOptions;
use App\Models\Properties;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class ContractsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->get('perPage') == "all") {
            $contracts = Contracts::with('additional_options', 'property_details', 'user_details')->all();
            if (!$contracts->isEmpty()) {
                foreach ($contracts as $key => $value) {
                    if ($value->terms_and_condition_file != null) {
                        $value->terms_and_condition_file = "/contracts/terms_and_condition/" . $value->terms_and_condition_file;
                    }
                    //$value->property_details->manager_image = !empty($contract->property_details->manager_image) ? "/new_images/" . $contract->property_details->manager_image : "/images/no_image.jpg";
                }
            }
        } else {
            $contracts = Contracts::with('additional_options', 'property_details', 'user_details')->paginate($request->get('perPage'));
            if (!$contracts->isEmpty()) {
                foreach ($contracts as $key => $value) {
                    if ($value->terms_and_condition_file != null) {
                        $value->terms_and_condition_file = "/contracts/terms_and_condition/" . $value->terms_and_condition_file;
                    }
                    //$value->property_details->manager_image = !empty($contract->property_details->manager_image) ? "/new_images/" . $contract->property_details->manager_image : "/images/no_image.jpg";
                }
            }
        }
        return $this->sendResponse($contracts->toArray(), 'Contracts fetched successfully');
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
            'property_id' => 'required',
            'start_date' => 'required',
            'contract_length' => 'required',
            'layout' => 'required',
            'contract_length_id' => 'required',
            'layout_id' => 'required',
            'capacity' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $check_contract = Contracts::where('property_id', $post['property_id'])->where('user_id', Auth::user()->id)->where('approved', 0)->orderBy('id', 'desc')->first();
        if (empty($check_contract)) {
            $contract = Contracts::create(
                [
                    'property_id' => $post['property_id'],
                    'start_date' => $post['start_date'],
                    'contract_length' => $post['contract_length']['length'],
                    'layout' => $post['layout']['name'],
                    'capacity' => $post['capacity'],
                    'user_id' => Auth::user()->id,
                    'contract_length_price' => $post['contract_length_price'] ?? 0,
                    'layout_price' => $post['layout_price'],
                    'final_price' => $post['final_price'],
                    'contract_length_id' => $post['contract_length_id'],
                    'layout_id' => $post['layout_id'],
                    'cost_per_person' => $post['cost_per_person'],
                    'last_revised' => now(),
                ]
            );
            if ($contract) {

                // near by amenities store
                if (isset($post['additionalOptions'])) {
                    $additionalOptions = ($post['additionalOptions']);
                    foreach ($additionalOptions as $key => $value) {
                        $contractAdditionalOptions = ContractsAdditionalOptions::create(
                            [
                                'contract_id' => $contract->id,
                                'additional_options_id' => $value['id'],
                                'price' => $value['price'],
                                'created_at' => now(),
                            ]
                        );

                        if (!$contractAdditionalOptions) {
                            $contract->delete();
                        }
                    }
                }

                $property = Properties::find($post['property_id']);
                if ($property) {
                    // $property->is_available = 0;
                    // $property->save();
                    //$contact_us = ContactUs::find($contact_us->id);
                    $admin_users = User::where('role', 'admin')->get();
                    if (!$admin_users->isEmpty()) {
                        foreach ($admin_users as $user) {
                            Mail::send('emails.new_contract', array(), function ($message) use ($request, $user) {
                                $message->to($user->email, $user->name)->subject('New Contract');
                                $message->from("engineering@extraslice.com", "ExtraSlice");
                            });
                        }
                    }

                    return $this->sendResponse($contract, 'Contract created successfully');
                } else {
                    $contract->delete();
                    return $this->sendError('Error', 'Error in storing contract', 402);
                }
            } else {
                return $this->sendError('Error', 'Error in storing contract', 402);
            }
        } else {
            //$contract = Contracts::with('additional_options', 'property_details', 'user_details')->where('id', $check_contract->id)->first();
            if (Auth::user()->role == 'admin') {
                $contract = Contracts::with('additional_options', 'property_details', 'user_details')->where('id', $check_contract->id)->first();
            } else {
                $contract = Contracts::with('additional_options', 'property_details', 'user_details')->where('id', $check_contract->id)->where('user_id', Auth::user()->id)->first();
            }

            if ($contract) {
                unset($post['property_id']);
                // unset($post['start_date']);
                unset($post['additionalOptions']);

                $post['contract_length'] = $post['contract_length']['length'];
                $post['layout'] = $post['layout']['name'];
                $post['contract_length_price'] = $post['contract_length_price'] ?? 0;

                $contract->update($post);
                // additional options update
                if (isset($request->additionalOptions)) {
                    $additionalOptionsContract = ContractsAdditionalOptions::where('contract_id', $contract->id)->delete();
                    $additionalOptions = ($request->additionalOptions);
                    foreach ($additionalOptions as $key => $value) {
                        $contractAdditionalOptions = ContractsAdditionalOptions::create(
                            [
                                'contract_id' => $contract->id,
                                'additional_options_id' => $value['id'],
                                'price' => $value['price'],
                                'created_at' => now(),
                            ]
                        );
                    }
                }

                $contract->property_details->manager_image = !empty($contract->property_details->manager_image) ? "/new_images/" . $contract->property_details->manager_image : "/images/no_image.jpg";
                $contract_offer = ContractOffer::where('user_id', Auth::user()->id)->where('contract_id', $check_contract->id)->whereIn('status', array('pending', 'approved'))->orderBy('id', 'desc')->first();
                if (empty($contract_offer)) {
                    $contract->allow_submit = 0;
                    $contract->offer_details = $contract_offer;
                } else {
                    $contract->allow_submit = 1;
                    $contract->offer_details = $contract_offer;
                }
                $approve_offer = ContractOffer::where('user_id', Auth::user()->id)->where('contract_id', $check_contract->id)->whereIn('status', array('approved'))->orderBy('id', 'desc')->first();
                if (empty($approve_offer)) {
                    $contract->approved_offer = 0;
                    //$contract->offer_details = $contract_offer;
                } else {
                    $contract->approved_offer = 1;
                    //$contract->offer_details = $contract_offer;
                }
                $property = Properties::find($contract->property_id);
                if ($property && $contract->terms_and_condition_file == null) {
                    $contract->terms_and_condition_file = "/terms_and_condition/" . $property->terms_and_condition_file;
                    // $contract->terms_and_condition_file =  "/contracts/terms_and_condition/".$contract->terms_and_condition_file;

                    return $this->sendResponse($contract, 'contract fetched successfully');
                } else if ($property && $contract->terms_and_condition_file != null) {
                    $contract->terms_and_condition_file = "/contracts/terms_and_condition/" . $contract->terms_and_condition_file;

                    return $this->sendResponse($contract, 'contract fetched successfully');
                } else {
                    return $this->sendError('Error', 'Record not found', 404);
                }
            } else {
                return $this->sendError('Error', 'Record not found', 404);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Contracts  $amenities
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Auth::user()->role == 'admin') {
            $contract = Contracts::with('additional_options', 'property_details', 'user_details')->where('id', $id)->first();
        } else {
            $contract = Contracts::with('additional_options', 'property_details', 'user_details')->where('id', $id)->where('user_id', Auth::user()->id)->first();
        }

        if ($contract) {
            $contract->property_details->manager_image = !empty($contract->property_details->manager_image) ? "/new_images/" . $contract->property_details->manager_image : "/images/no_image.jpg";
            $contract_offer = ContractOffer::where('user_id', Auth::user()->id)->where('contract_id', $id)->whereIn('status', array('pending', 'approved'))->orderBy('id', 'desc')->first();
            if (empty($contract_offer)) {
                $contract->allow_submit = 0;
                $contract->offer_details = $contract_offer;
            } else {
                $contract->allow_submit = 1;
                $contract->offer_details = $contract_offer;
            }
            $approve_offer = ContractOffer::where('user_id', Auth::user()->id)->where('contract_id', $id)->whereIn('status', array('approved'))->orderBy('id', 'desc')->first();
            if (empty($approve_offer)) {
                $contract->approved_offer = 0;
            } else {
                $contract->approved_offer = 1;
            }
            $property = Properties::find($contract->property_id);
            if ($property && $contract->terms_and_condition_file == null) {
                $contract->terms_and_condition_file = "/terms_and_condition/" . $property->terms_and_condition_file;
                // $contract->terms_and_condition_file =  "/contracts/terms_and_condition/".$contract->terms_and_condition_file;

                return $this->sendResponse($contract, 'contract fetched successfully');
            } else if ($property && $contract->terms_and_condition_file != null) {
                $contract->terms_and_condition_file = "/contracts/terms_and_condition/" . $contract->terms_and_condition_file;

                return $this->sendResponse($contract, 'contract fetched successfully');
            } else {
                return $this->sendError('Error', 'Record not found', 404);
            }
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
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
        $contract = Contracts::with('additional_options', 'property_details', 'user_details')
                    ->findOrFail($id);

        if ($contract) {
            unset($post['property_file']);
            unset($post['property_details']);
            unset($post['user_details']);
            unset($post['approved_offer']);
            unset($post['offer_details']);
            $contract->update($post);

            if ($contract->signed == 1 && $contract->approved == 1 && $contract->first_rent == 1 && $contract->last_rent == 1 && $contract->add_on_cost == 1 && $contract->materials_ordered == 1 && $contract->assembly_started == 1 && $contract->setup_completed == 1 && $contract->send_via_fedex == 1 && $contract->arrived_in_mail == 1) {
                $contract->is_contract_done = 1;
                $contract->save();

                Properties::where('id', $post['property_id'])->update(['is_available' => 0]);
                $user = User::find($contract->user_id);
                if ($user) {
                    $data['user'] = $user;
                    //Mail send to user
                    //print_r( env("MAIL_USERNAME") ); exit;
                    Mail::send('emails.contract-success', $data, function ($message) use ($user) {
                        $message->to($user->email)->subject('Contract success notification');
                    });
                }
            }
            return $this->sendResponse($contract, 'contracts updated successfully');
        } else {
            return $this->sendError('Error', 'contracts does not exist', 404);
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
        $contract = Contracts::find($id);
        if (!$contract) {
            return $this->sendError('Error', 'Record not found', 404);
        } else {
            $contractFile = $contract->terms_and_condition_file;

            if ($contract->delete()) {
                if ($contractFile) {
                    $filename = public_path() . '/contracts/terms_and_condition/' . $contractFile;
                    \File::delete($filename);
                }
                $contracts = Contracts::with('additional_options', 'property_details', 'user_details')->paginate(5);
                if (!$contracts->isEmpty()) {
                    foreach ($contracts as $key => $value) {
                        if ($value->terms_and_condition_file != null) {
                            $value->terms_and_condition_file = "/contracts/terms_and_condition/" . $value->terms_and_condition_file;
                        }
                        //$value->property_details->manager_image = !empty($contract->property_details->manager_image) ? "/new_images/" . $contract->property_details->manager_image : "/images/no_image.jpg";
                    }
                }
                return $this->sendResponse($contracts->toArray(), 'Contracts deleted successfully');
            } else {
                return $this->sendError('Error', 'Error in deletion', 500);
            }
        }
        return $this->sendError('Error', 'Error in deletion', 500);
    }

    public function getByUser($id)
    {
        $contract = Contracts::whereHas('property_details')
                    ->with(['additional_options','property_details:id,title'])->where('user_id', $id)->get();
        if ($contract) {
            return $this->sendResponse($contract, 'contract fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    public function getByUserProperty($id, $property)
    {
        $contract = Contracts::with('additional_options.options')->where('user_id', $id)->where('property_id', $property)->first();
        if ($contract) {
            return $this->sendResponse($contract, 'contract fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    public function contractsFileUpload(Request $request)
    {
        $post = $request->all();

        $contract = Contracts::find($request->id);
        if ($contract) {
            // for conditional file upload
            $file = time() . '.' . $request->attachments->getClientOriginalExtension();

            $request->attachments->move(public_path('contracts/terms_and_condition'), $file);

            $contract->terms_and_condition_file = $file;
            $contract->last_revised = now();
            $contract->save();

            $contract->terms_and_condition_file = "/contracts/terms_and_condition/" . $contract->terms_and_condition_file;

            return $this->sendResponse($contract, 'contract fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }
}
