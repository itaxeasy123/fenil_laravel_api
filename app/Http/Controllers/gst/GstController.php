<?php

namespace App\Http\Controllers\gst;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class GstController extends Controller
{
    //
    use ResponseAPI;

    public function gstStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'gstin' => 'required',
                'year' => 'required',
                'month' => 'required',
                'reference_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->error($validator->errors());
            }
            $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . 'gsp/tax-payer/' . $request->gstin . '/gstrs\/' . $request->year . '/' . $request->month . '/status?reference_id=' . $request->reference_id);
            $data = json_decode($response);
            return response()->json($data, $data->code);
        } catch (\Exception $e) {
            return response()->json(['error' => "Something went wrong"], 400);
        }
    }

    public function searchDetailsByGSTINNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/public/gstin/' . $request->gstin);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function searchGSTINNumberByPan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_state_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/public/pan/' . $request->pan . '?state_code=' . $request->gst_state_code);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function trackGSTReturn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'financialYear' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/public/gstr?gstin=' . $request->gstin . '&financial_year=' . $request->financialYear);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }


    public function registerForGST(Request $request)
    {
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/registration', $request->all());
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function generateOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            // 'gst_portal_username' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        generateNewSandboxToken();
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer' . $request->gstin . '?username=' . $request->gst_portal_username);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'gst_portal_username' => 'required',
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/otp/verify?username=' . $request->gst_portal_username . '&otp' . $request->otp);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function uploadGSTR4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-4/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function getGstr3bSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-3b/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function uploadGstr3b(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-3b/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function submitGstr3b(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-3b/' . $request->year . '/' . $request->month . '/submit');
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr2a(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-2a/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr2aB2B(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-2a/b2b/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr2aB2BA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-2a/b2ba/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr2aCDN(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-2a/cdn/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr2aCDNA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-2a/cdna/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr2aISD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-2a/isd/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1AT(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'gstin' => 'required',
                'year' => 'required',
                'month' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $response = Http::withHeaders(withSandBoxHeader())->get(
                env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/at/' . $request->year
                    . '/' . $request->month
            );
            $data = json_decode($response);
            return response()->json($data, $data->code);
        } catch (\Exception $e) {
            return response()->json(['error' => "Something went wrong"], 400);
        }
    }

    public function gstr1ATA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(
            'https://api.sandbox.co.in/gsp/tax-payer/gstin/gstrs/gstr-1/ata/year/month'
        );
        $data = json_decode($response);
        return response()->json($data, 200);
    }

    public function gstr1B2B(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
            'filter' => 'required',
            'from' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/b2b/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin . '&action_required' . $request->filter . '&from=' . $request->from);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1B2BA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'ctin' => 'required',
            'filter' => 'required',
            'from' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/b2b/' . $request->year . '/' . $request->month . '?ctin=' . $request->ctin . '&action_required' . $request->filter . '&from=' . $request->from);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1B2CL(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'state_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/b2cl/' . $request->year . '/' . $request->month . '?state_code=' . $request->state_code);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1B2CLA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'state_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/b2cla/' . $request->year . '/' . $request->month . '?state_code=' . $request->state_code);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1B2CS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/b2cs/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1B2CSA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/b2csa/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1CDNR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'filter' => 'required',
            'from' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/cdnr/' . $request->year . '/' . $request->month . '?action_required=' . $request->filter . '&from=' . $request->from);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1CDNRA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'filter' => 'required',
            'from' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/cdnra/' . $request->year . '/' . $request->month . '?action_required=' . $request->filter . '&from=' . $request->from);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1CDNUR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/cdnur/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1CDNURA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/cdnura/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1DocIssue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/doc-issue/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1Exp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/exp/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1Expa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/expa/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1Summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1HSN(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/hsn/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function gstr1NIL(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/nil/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function uploadGSTR1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/' . $request->year . '/' . $request->month);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function submitGSTR1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/' . $request->year . '/' . $request->month . '/submit');
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }

    public function fileGSTR1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => 'required',
            'year' => 'required',
            'month' => 'required',
            'authorized_signatory_pan' => 'required',
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/gsp/tax-payer/' . $request->gstin . '/gstrs/gstr-1/' . $request->year . '/' . $request->month . '/file?pan=' . $request->authorized_signatory_pan . '&otp=' . $request->otp);
        $data = json_decode($response);
        return response()->json($data, $data->code);
    }
}
