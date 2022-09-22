<?php

namespace App\Http\Controllers\mca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class McaController extends Controller
{
    //
    use ResponseAPI;
    
    public function getCompanyByCIN(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $response = Http::withHeaders(withSandBoxHeader())->get(env('SANDBOX_BASE_URL').'/mca/companies/'.$request->id);
        $data = json_decode($response);
        return $this->success($data);
    }
}
