<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Http\Controllers\Controller;
use App\Models\Manager\FireDrillsAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FireDrillsAttendanceController extends Controller
{
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
        $data = $request->validated();
        $data["attended_by"] = Auth::user()->id;

        $fireDrill = FireDrillsAttendance::create($data);

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
