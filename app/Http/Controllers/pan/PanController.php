<?php

namespace App\Http\Controllers\pan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Traits\ResponseAPI;

class PanController extends Controller
{
      use ResponseAPI;
    public function checkPanAADHARStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aadhar' => 'required',
            'pan' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/pans/' . $request->pan . '.toUpperCase()/pan-aadhaar-status?aadhaar_number=' .$request->aadhar);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function getAdvancePanDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [            
            'pan' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/pans/' . $request->pan . '.toUpperCase()?consent=Y&reason=For KYC of User');
        $data = json_decode($response);
        return $this->success($data);
    }
}
