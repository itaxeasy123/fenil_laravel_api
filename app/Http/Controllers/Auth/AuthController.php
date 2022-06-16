<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

use function GuzzleHttp\Promise\all;

class AuthController extends Controller
{
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
}
