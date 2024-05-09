<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditService;

class AuditController extends Controller
{
    public function __construct(private AuditService $service)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->response('Audit listing', $this->service->index($request), 200);
    }
}
