<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

function getAccessToken()
{
    //sanbox_access_token
    $token = Setting::select('value')->where('key', 'access_token')->first();
    echo $token->value;
    return $token->value;
}

function withSandBoxHeader()
{
    // if (isTokenExpired(getAccessToken()) == false) {
    //     generateNewSandboxToken();
    // }
    $data = [
        'x-api-key' => env('SANDBOX_KEY'),
        'Authorization' => generateNewSandboxToken(),
        'x-api-version' => env('SANDBOX_API_VERSION'),
        'x-api-secret' => env('SANDBOX_SECRET')
    ];

    return $data;
}

function isTokenExpired($token)
{
    $tokenParts = explode(".", $token);
    $tokenHeader = base64_decode($tokenParts[0]);
    $tokenPayload = base64_decode($tokenParts[1]);
    $jwtHeader = json_decode($tokenHeader);
    $jwtPayload = json_decode($tokenPayload);
    $currentTimeStamp =  Carbon::now()->timestamp / 1000;
    return $jwtPayload->exp < $currentTimeStamp;
}

function generateNewSandboxToken()
{
    $data = [
        'x-api-key' => env('SANDBOX_KEY'),
        'x-api-secret' => env('SANDBOX_SECRET'),
        'x-api-version' => env('SANDBOX_API_VERSION')
    ];
    $response = Http::withHeaders($data)->post(env('SANDBOX_BASE_URL') . '/authenticate');
    $token = json_decode($response);
    $update_token = Setting::where('key', 'access_token')->update(['value' => $token->access_token]);
    return $token->access_token;
}
