<?php

namespace App\Http\Controllers\ManagerControllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use App\Http\Controllers\Controller;
use App\Models\CheckIn;

class DashboardController extends Controller
{

    public function __construct(private DashboardService $service)
    {
        //
    }

    public function getStats()
    {
        $data = $this->service->getStats();

        return $this->response('Dashboard Stats Data', $data, 200);
    }

    public function getRiskStats()
    {

        $data = $this->service->getRiskStats();

        return $this->response('Dashboard Stats Data', $data, 200);
    }

    public function getCurrentLevelRiskStats(Request $request)
    {
        $data = $this->service->getCurrentLevelRiskStats($request);

        return $this->response('Dashboard Stats Data', $data, 200);
    }


    public function getRisksData(Request $request)
    {
        $data = $this->service->getRisksData($request);

        return $this->response('Dashboard Stats Data', $data, 200);
    }

    public function getAttendance()
    {
        $today = Carbon::today()->format('Y-m-d');
        $attendance = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->orderBy('id', 'desc')->first();
        $time_logs = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->get();
        $data = [
            'attendance' => $attendance,
            'time_logs' => $time_logs
        ];
        return $this->response('Dashboard Stats Data', $data, 200);
    }
}
