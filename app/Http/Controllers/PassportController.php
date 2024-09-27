<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Mail\VerifyEmail;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Mail;
use Validator;

class PassportController extends BaseController
{
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users',
            // 'mobile' => 'required|digits_between:6,10',
            'password' => 'required',
            // 'c_password' => 'required|same:password'
            'profile_image' => 'nullable|mimes:jpeg,png,jpg'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        DB::beginTransaction();

        try {
            $filename = null;
            if ($request->hasFile('profile_image')) {

                if(!is_dir(public_path('profile_images/'))) {
                    mkdir(public_path('profile_images/'), 0755, true);
                }
                $filename = 'profile_image_' . time() . '.' . $post['profile_image']->getClientOriginalExtension();
                $filetype = $post['profile_image']->getClientOriginalExtension();
                $post['profile_image']->move(public_path('profile_images/'), $filename);
            }


            $user = User::create([
                        'name' => $post['name'],
                        'email' => $post['email'],
                        // 'mobile' => $post['mobile'],
                        'password' => bcrypt($post['password']),
                        'role' => 'user',
                        'verification_token' => Hash::make($request->email),
                        'token_expire_at' => now()->addDays(env('EMAIL_TOKEN_EXPIRY_DAYS')),
                        'profile_image' => $filename
                    ]);

            // Mail::to($user->email)->send(new VerifyEmail($user));

            DB::commit();

            return response()->json([
                'msg'    => 'Email verification link sent successfully !',
                'status' => 'success',
            ], 200);

        } catch (\Exception$e) {
            DB::rollback();
            Log::error('Failed to register user ' . $request->email . ' reason:' . $e->getMessage());
            return response()->json(['msg' => 'Failed to register user !', 'status' => 'error'], 500);
        }
    }

    /**
     * This method is used to verify user's email
     * @param Request $request
     * @param $email
     * @return Response json
     */

    public function verifyEmail(Request $request)
    {

        if (!$request->token) {
            return response()->json(["msg" => "Invalid url provided.", 'status' => 'fail'], 401);
        }

        $user = User::where('verification_token','=',$request->token)->first();

        if (!$user) {
            return response()->json(["msg" => "Invalid token", "status" => "fail"], 404);
        }

        if (!$user->hasVerifiedEmail()) {

            if(now() >= $user->token_expire_at){
                return response()->json(["msg" => "Link is expired !", "status" => "fail"], 401);
            }

            $user->markEmailAsVerified();

            $token = $user->createToken(config('app.name'))->accessToken;

            $data['user'] = $user;
            $data['token'] = $token;

            if($user) {
                Mail::send('emails.signupComplete', $data, function($message) use ($user)
                {
                    $message->to($user['email'])->subject('Registration Completed !');
                });
            }

            return $this->sendResponse($data, 'User created successfully');

        } else {
            return response()->json(["msg" => "Email already verified !", "status" => "success"], 200);
        }

        if (!$request->hasValidSignature()) {
            return response()->json(["msg" => "Email verification succcess", "status" => "success"], 200);
        }

    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        // return $request;
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Password is incorrect'], 401);
        }

        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json(['error' => 'Please verify your email'], 401);
        // }

        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken(config('app.name'))->accessToken;
            return response()->json(['token' => $token, 'user' => auth()->user()], 200);
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
    }
    public function social_login(Request $request)
    {

        // return $request;
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'provider' => 'required',
            'accessToken' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $providerUser = Socialite::driver($request->provider)->userFromToken($request->accessToken);

        $user = User::where('email', $providerUser->email)->first();
        if (empty($user)) {

            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890~!@#$%^&*()';
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            $data['password'] = implode($pass);

            $user = new User();
            $user->email = $providerUser->email;
            $user->role = 'user';
            $user->name = $providerUser->name;
            $user->email_verified_at = now();
            $user->password = bcrypt($data['password']);
            $user->save();

            $data['user_name'] = $providerUser->name;
            $data['provider_name'] = $request->provider;
            Mail::send('emails.social_login_email', $data, function ($message) use ($request) {
                $message->to($request->email)->subject('Social Login Successful');
            });
        }
        Auth::login($user);



        if (!empty(Auth::user()->id)) {
            $token = Auth::user()->createToken(config('app.name'))->accessToken;
            return response()->json(['token' => $token, 'user' => auth()->user()], 200);
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }


    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::where('email', $request->email)
                    // ->where('email_verified_at','!=',NULL)
                    ->first();

        if ($user) {
            $data['user'] = $user;
            $data['token'] = substr(md5(mt_rand()), 0, 30);
            $data['host'] = $request->getHttpHost();
            // Mail::send('emails.resetPassword', $data, function ($message) use ($request) {
            //     $message->to($request->email)->subject('Reset Password');
            // });
            // user token data enter in table
            $userToken['user_id'] = $user->id;
            $userToken['token'] = $data['token'];
            $userToken['created_at'] = now();
            DB::table('users_token')->insert($userToken);

            return $this->sendResponse('', 'Email for reset password is sent! kindly check your mail!');
        } else {
            return $this->sendError('User not found!');
        }
    }

    // for reset password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'c_password' => 'required|same:password',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $userToken = DB::table('users_token')->where('token', $request->token)->first();
        if ($userToken) {
            $user = User::find($userToken->user_id);
            if ($user) {
                $user->password = bcrypt($request->password);
                $user->save();

                DB::table('users_token')->where('token', $request->token)->delete();
            }

            $data['user'] = $user;
            $data['token'] = substr(md5(mt_rand()), 0, 30);

            Mail::send('emails.passwordResetSuccess', $data, function ($message) use ($user) {
                $message->to($user->email)->subject('Your reset password information');
            });

            return $this->sendResponse($user, 'Your Password reset successfully');
        } else {
            return $this->sendError('Invalid Token!');
        }
    }
}
