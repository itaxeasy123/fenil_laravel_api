<?php

namespace App\Http\Controllers;

use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class LedgerController extends Controller
{
    use ResponseAPI;

    public function cashITCBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . 'gsp/tax-payer/' . $request->gstin . '/ledgers/bal/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function cashLedger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . 'gsp/tax-payer/' . $request->gstin . '/ledgers/cash?from=' . $request->from . '&to=' . $request->to);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function itcLedger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/ledgers/itc?from=' . $request->from . '&to=' . $request->to);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function taxLiabilityLedger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . 'gsp/tax-payer/' . $request->gstin . '/ledgers/tax/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function otherLedger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            // 'from' => 'required',
            // 'to' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . 'gsp/tax-payer/' . $request->gstin . '/ledgers/other?from=' . $request->from . '&to=' . $request->to);
        $data = json_decode($response);
        return $this->success($data);
    }

    public function returnRelatedLiabilityBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'gstr' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . 'gsp/tax-payer/' . $request->gstin . '/ledgers' . "/" . $request->gstr . '/liability' . "/" . $request->year . "/" .  $request->month);
        $data = json_decode($response);
        return $this->success($data);
    }
}
