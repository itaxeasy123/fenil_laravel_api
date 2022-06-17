<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseAPI
{
    /**
     * Core of response
     *
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode
     * @param   boolean         $isSuccess
     */


    private function coreResponse(array $data, int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    public function success($contents, $is_data = true)
    {
        $data = ['success' => true];
        if ($is_data) {
            $data['results'] = $contents;
        } else {
            $data = array_merge($data, $contents);
        }

        return $this->coreResponse($data);
    }



    public function error($message, $code = 422): JsonResponse
    {
        return $this->coreResponse(
            ['success' => false, 'message' => $message],
            $code
        );
    }


}
