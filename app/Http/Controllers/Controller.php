<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function response($msg='ok', $data=null, $code=200)
    {
        return response()->json([
            'message' => $msg,
            'data'=> $data,
            'status'=>$code
        ], $code);
    }

    public function error($msg='Something went wrong', $code=500)
    {
        return response()->json([
            'message' => $msg,
            'status'=>$code,
            'data' => []
        ], $code);
    }
}
