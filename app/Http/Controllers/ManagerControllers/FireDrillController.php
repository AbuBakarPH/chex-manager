<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Events\FireDrillAlert;
use App\Http\Controllers\Controller;
use App\Models\Manager\FireDrill;
use App\Models\User;
use App\Services\GeneralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FireDrillController extends Controller
{
    public function __construct(private GeneralService $generalService)
    {
        $this->generalService = $generalService;
        // parent::__construct($model);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'note' => 'nullable|string',
        ]);
        $data["user_id"] = Auth::user()->id;

        $tokens = User::where('company_id', Auth::user()->company_id)->where('device_token', '!=', null)->get();
        $fireDrill = FireDrill::create($data);
        $this->generalService->sendFCMNotification($tokens, 'Fire Drill Notification', $data["note"]);
        return response(
            $fireDrill,
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
