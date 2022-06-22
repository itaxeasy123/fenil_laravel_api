<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserForgotPasswordMail;
use App\Models\Admin;
use App\Models\PasswordReset;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Traits\ResponseAPI;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function GuzzleHttp\Promise\all;

class AuthController extends Controller
{
    use ResponseAPI;
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        try {
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (ClientException $exception) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        $userCreated = User::firstOrCreate(
            [
                'email' => $user->getEmail()
            ],
            [
                'first_name' => $user->getName() ?? "Test",
                'last_name' => $user->getName() ?? "Test",
                'password' => '1234',
                'email_verified_at' => now(),
            ]
        );
        $userCreated->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $user->getId(),
            ],
            [
                'avatar' => $user->getAvatar()
            ]
        );
        $token = $userCreated->createToken('auth-socialite')->accessToken;
        $success['token'] = $token;
        $success['user'] = $userCreated;

        return response()->json($success, 200);
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'github', 'google', 'twitter'])) {
            return response()->json(['error' => 'Please login using facebook, twitter, github or google'], 422);
        }
    }

    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $user = User::firstOrCreate(
            [
                'email' => $request->email
            ],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => encrypt($request->password),
                'email_verified_at' => now(),
            ]
        );

        $token = $user->createToken('auth-socialite')->accessToken;
        $success['status'] = 200;
        $success['message'] = "Registration Successful !";
        $success['data'] = $user;
        $success['token'] = $token;

        return response()->json($success, 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:users,email',
            'password'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $user = User::where(['email' => $request->email])->first();
        if (decrypt($user->password) == $request->password) {
            $token = $user->createToken('auth-socialite')->accessToken;
            $success['status'] = 200;
            $success['message'] = "Login Successful !";
            $success['data'] = $user;
            $success['token'] = $token;
            return response()->json($success, 200);
        }
        return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => "Invalid Password !"], 401);
    }

    public function adminSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required|email|unique:admins,email',
            'password'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $admin = Admin::firstOrCreate(
            [
                'email' => $request->email
            ],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => encrypt($request->password),
            ]
        );

        $token = $admin->createToken('admin')->accessToken;
        $success['status'] = 200;
        $success['message'] = "Admin Registration Successful !";
        $success['data'] = $admin;
        $success['token'] = $token;

        return response()->json($success, 200);
    }

    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:admins,email',
            'password'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $admin = Admin::where(['email' => $request->email])->first();
        if (decrypt($admin->password) == $request->password) {
            Auth::login($admin);
            $token = $admin->createToken('admin')->accessToken;
            $success['status'] = 200;
            $success['message'] = "Login Successful !";
            $success['data'] = $admin;
            $success['token'] = $token;
            return response()->json($success, 200);
        }
        return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => "Invalid Password !"], 401);
    }
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            if ($request->allDevice) {
                $user->tokens->each(function ($token) {
                    $token->delete();
                });
                return $this->success('Logged Out from all devices !!');
            }
            $userToken = $user->token();
            $userToken->delete();
            return $this->success('Logged Out Successfully !!');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $token              = rand(111111, 999999);
        $token_expire_at    = Carbon::now()->addMinutes(config('constants.mail_expiration_minutes'))->format('Y-m-d H:i:s');

        try {
            $details = [
                'subject'   => 'Forgot Password Token',
                'token'     => $token
            ];
            // send email
            Mail::to($request->email)->send(new UserForgotPasswordMail($details));

            $user = User::where(['email'=>$request->email])->first();
            PasswordReset::create([
                'user_id'               => $user->id,
                'email'                 => $user->email,
                'token'                 => $token,
                'mail_token_expire_at'  => $token_expire_at
            ]);

            $response['success']  = true;
            // $response['otp']      = $token;
            $response['message']  = 'OTP sent successfully!';
        } catch (Exception $e) {
            Log::debug("Code: " . $e->getCode() . " Line: " . $e->getLine() . " Message: " . $e->getMessage());
            $response["message"] = $e->getMessage();
        }
        return $response;
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:users,email',
            'otp'         => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $passwordResetData = PasswordReset::where(['email' => $request->email, 'token' => $request->otp])->latest()->first();
        if (!empty($passwordResetData)) {
            if ($passwordResetData->mail_token_expire_at < Carbon::now()) {
                $this->response['message'] = 'Your token has expired.Please generate new token!';
            } else {
                PasswordReset::where('email', $request->email)->update(['token' => ""]);
                $this->response['success'] = true;
                $this->response['message'] = 'Set a new password.';
            }
        } else
            $this->response['message'] = 'The OTP entered is incorrect.';

        return $this->response;
    }

    public function setNewPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => "Error Occured !", "error" => $validator->errors()], 401);
        }

        $user = User::where([
            'email' => $request->email
        ])->update(['password' => encrypt($request->password)]);
        if ($user) {
            $this->response['success'] = true;
            $this->response['message'] = 'Password updated successfully.';
        } else
            $this->response['message'] = 'These credentials do not match our records.';

        return $this->response;
    }
}
