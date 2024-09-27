<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Log;

class UsersController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->get('perPage') == "all") {
            $users = User::where('id','!=',auth()->id())->all();
        } else {
            $users = User::where('id','!=',auth()->id())->paginate($request->get('perPage'));
        }

        return $this->sendResponse($users->toArray(), 'Users fetched successfully');
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::create(
            [
                'name' => $post['name'],
                'email' => $post['email'],
                'mobile' => $post['mobile'],
                'password' => bcrypt($post['password']),
                'role' => $post['role'],
                'email_verified_at' => now()
            ]
        );

        $data['user'] = $user;

        if($user) {

            Mail::send('emails.signupComplete', $data, function($message) use ($post)
            {
                $message->to($post['email'])->subject('Registration Completed');
            });
        }

        $user = User::paginate(5);

        return $this->sendResponse($user, 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if($user) {
            return $this->sendResponse($user, 'User fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function edit(User $User)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); //Get content category specified by id
        if($user) {
            $post = $request->all();

            $validator = Validator::make($post, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,'.$user->id,
                'mobile' => 'required',
                'role' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            $user->update($post);

            return $this->sendResponse($user, 'User updated successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if(!$user) {
            return $this->sendError('Error', 'Record not found', 404);
        }
        if($user->delete()){
            $user = User::paginate(5);
            return $this->sendResponse($user,'User deleted successfully');
        }
        return $this->sendError('Error', 'Error in deletion', 500);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id); //Get content category specified by id
        if($user) {
            $post = $request->all();

            $validator = Validator::make($post, [
                'name' => 'max:50',
                'email' => 'email|unique:users,email,'.$user->id,
                'profile_image' => 'nullable|mimes:jpeg,png,jpg',
                'current_password' => 'min:6',
                'new_password' => 'min:6',
                'confirm_password' => 'same:new_password|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 402);
            }

            if ($request->hasFile('profile_image')) {

                if(!is_dir(public_path('profile_images/'))) {
                    mkdir(public_path('profile_images/'), 0755, true);
                }
                $filename = 'profile_image_' . time() . '.' . $post['profile_image']->getClientOriginalExtension();
                $filetype = $post['profile_image']->getClientOriginalExtension();
                $post['profile_image']->move(public_path('profile_images/'), $filename);
                $post['profile_image'] = $filename;
            }


            if(isset($post['new_password'])){
                $post['password'] = bcrypt($post['new_password']);
            }

            if(isset($post['current_password']) && $post['new_password']){
                if (Hash::check($post['current_password'], $user->password)) {
                    $post['password'] = bcrypt($post['new_password']);

                    $data['user'] = $user;

                    // Mail::send('emails.passwordResetSuccess', $data, function ($message) use ($user) {
                    //     $message->to($user->email)->subject('Your reset password information');
                    // });
                }else{
                    return $this->sendResponse($user, 'Your Current Password is not matched');
                }
            }

            if(isset($post['email']) && $post['email'] != auth()->user()->email){

                $post['email_verified_at']  = null;
                $post['verification_token'] = Hash::make($request->email);
                $post['token_expire_at']    = now()->addDays(env('EMAIL_TOKEN_EXPIRY_DAYS'));

                // Mail::to($post['email'])->send(new VerifyEmail($post));
            }

            $user->update($post);

            if(isset($post['email']) && $post['email'] != auth()->user()->email){
                return $this->sendResponse($user, 'User updated successfully and Email verification link sent, Please verify new email Address!');
            }else{
                return $this->sendResponse($user, 'User updated successfully');
            }
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if($user) {
            $post = $request->all();
            $validator = Validator::make($post, [
                'current_password' => 'required|min:6',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            if (Hash::check($request->current_password, $user->password)) {
                $user->password = bcrypt($request->new_password);
                $user->save();

                $data['user'] = $user;

                Mail::send('emails.passwordResetSuccess', $data, function ($message) use ($user) {
                    $message->to($user->email)->subject('Your reset password information');
                });

                return $this->sendResponse($user, 'Your Password reset successfully');
            }else{
                return $this->sendResponse($user, 'Your Current Password is not matched');
            }
        } else {
            return $this->sendError('Invalid Token!');
        }
    }
    public function getUserProfile()
    {
        $user = auth()->user();
        if($user) {
            return $this->sendResponse($user, 'User found successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }
}
