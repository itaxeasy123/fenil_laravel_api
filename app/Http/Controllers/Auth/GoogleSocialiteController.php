<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\User;



class GoogleSocialiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {

            $user = Socialite::driver('google')->user();

            $finduser = User::where('social_id', $user->id)->first();

            if ($finduser) {

                Auth::login($finduser);

                $token = $finduser->createToken('auth-socialite')->accessToken;
                $success['status'] = 200;
                $success['message'] = "Registration Successful !";
                $success['data'] = $finduser;
                $success['token'] = $token;
                return response()->json(['message' => 'Logged in', 'token' => $token], 200);
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'social_id' => $user->id,
                    'social_type' => 'google',
                    'password' => encrypt('my-google')
                ]);

                Auth::login($newUser);

                $token = $newUser->createToken('auth-socialite')->accessToken;
                $success['status'] = 200;
                $success['message'] = "Registration Successful !";
                $success['data'] = $newUser;
                $success['token'] = $token;
                return response()->json(['message' => 'Logged in'], 200);
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}