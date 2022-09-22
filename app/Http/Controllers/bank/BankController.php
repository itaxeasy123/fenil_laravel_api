<?php

namespace App\Http\Controllers\bank;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Exception;


class BankController extends Controller
{
    //

    use ResponseAPI;

    function getBankDetailsByIfsc(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ifsc' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->error($validator->errors());
            }
            $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/bank/' . $request->ifsc);
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    function verifyBankAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ifsc' => 'required',
                'accountNumber' => 'required',
                'name' => 'required',
                'mobile' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->error($validator->errors());
            }
            generateNewSandboxToken();
            $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/bank/' . $request->ifsc . '/accounts/' . $request->accountNumber . '/verify?name=' . $request->name . '&mobile=' . $request->mobile);
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
