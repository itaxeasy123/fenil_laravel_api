<?php

namespace App\Http\Controllers;

use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ChallanController extends Controller
{
    use ResponseAPI;


    public function addUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payer_id' => 'required',
            'challan' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_QUICKO_URL') . 'tds/payers/' . $request->payer_id . '/challans', [
            'challan' => $request->challan
        ]);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function fetchOne(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payer_id' => 'required',
            'challan_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/tds/payers/' . $request->payer_id . '/challans' . '/' . $request->challan_id);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function count(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_QUICKO_URL') . 'tds/payers/' . $request->payer_id . '/challans/count?challan_number=' . $request->challan_number);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function fetchMany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_QUICKO_URL') . 'tds/payers/' . $request->payer_id . '/challans?challan_number=' . $request->challan_number  . '&count=' . $request->count . '&skip=' . $request->skip);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payer_id' => 'required',
            'challan_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->delete(env('SANDBOX_QUICKO_URL') . 'tds/payers/' . $request->payer_id . '/challans' . '/' . $request->challan_id);
        $data = json_decode($response);
        return $this->success($data);
    }
}
