<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');
        $data = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->get();
        return $this->response('Time Logs fetched successfully', $data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $latest = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->orderBy('id', 'desc')->first();

        if (isset($latest["type"])) {
            if ($latest["type"] == "in") {
                $data['type'] = "out";
            } else {
                $data['type'] = "in";
            }
        } else {
            $data['type'] = "in";
        }
        $data['user_id'] = auth()->user()->id;

        $output["attendance"] = CheckIn::create($data);
        $output["time_logs"] = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->get();

        return $this->response('Attendance created successfully', $output, 200);
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
