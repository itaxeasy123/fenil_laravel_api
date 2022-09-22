<?php

namespace App\Http\Controllers\pincode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PincodeController extends Controller
{
    //
     use ResponseAPI;
    public function pincodebycity(Request $request){

        $validator = Validator::make($request->all(), [
            'city' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::get('https://api.postalpincode.in/pincode/'.$request->city);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function pincodeinfo(Request $request){

        $validator = Validator::make($request->all(), [
            'pincode' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::get('https://api.postalpincode.in/pincode/'.$request->pincode);
        $data = json_decode($response);
        return $this->success($data);
    }
}
