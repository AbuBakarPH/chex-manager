<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use App\Services\GeneralService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator as PaginationPaginator;

class AttendanceController extends Controller
{
    public function __construct(private GeneralService $generalService)
    {
        // parent::__construct($model);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $loggeduserRole =  Auth::user()->roles->pluck('name')[0];
        if ($loggeduserRole == 'Manager') {
            $staffIds = auth()->user()->company->staff()->pluck('id');

            if (isset($request->created_at)) {
                $createdAt = $request->created_at;
            } else {
                $createdAt = Carbon::today();
            }

            $users = User::whereIn('id', $staffIds)->whereHas('checkins', function ($q) use ($createdAt) {
                $q->whereDate('created_at', $createdAt);
            })->with(['checkins' => function ($q) use ($createdAt) {
                $q->whereDate('created_at', $createdAt);
            }])
                ->when($request['searchText'], function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request['searchText']}%");
                })
                ->when($request['name'], function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request['name']}%");
                })->get();

            $userDataList = [];

            foreach ($users as $user) {
                // Create a new $userData array for each user
                $userData = ['name' => $user->name];
                $firstCheckin = $user->checkins()->whereDate('created_at', $createdAt)->whereType('in')->first();
                if ($firstCheckin) {
                    $firstCheckinTime = $firstCheckin->created_at;
                    $userData['firstIn']    = $firstCheckinTime;
                }
                $latestCheckouts = $user->checkins()->whereDate('created_at', $createdAt)->whereType('out')->latest()->get();

                $userData['lastOut']   = NULL;
                $userData['totalHours'] = "00:00:00";

                if (($latestCheckouts->count() > 0)) {
                    $lastCheckout = $latestCheckouts[0];
                    $lastCheckOutTime = $lastCheckout->created_at;
                    $userData['lastOut']   = $lastCheckOutTime;
                    $userData['totalHours'] = $this->calculateTotalHours($user, $createdAt);
                }

                $userData['id']    = $user->id;
                $userDataList[] = $userData;
            }

            return $this->paginate($userDataList, $request['perpage'], $request['page']);
        }
    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (PaginationPaginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function calculateTotalHours($user, $createdAt)
    {
        // Retrieve time logs for today (assuming 'created_at' is a timestamp in your database)
        $timeLogs = $user->checkins()->whereDate('created_at', $createdAt)->get();

        if ($timeLogs->isEmpty()) {
            return "00:00:00"; // Default value if timeLogs is empty
        }

        $totalTimeInSeconds = 0;
        $inTime = null;

        foreach ($timeLogs as $log) {
            if ($log->type === "in") {
                $inTime = Carbon::parse($log->created_at);
            } elseif ($log->type === "out" && $inTime !== null) {
                $outTime = Carbon::parse($log->created_at);
                $totalTimeInSeconds += $outTime->diffInSeconds($inTime);
                $inTime = null;
            }
        }

        // Assuming $this->attendance->type is a property of the controller

        return Carbon::createFromTimestampUTC($totalTimeInSeconds)->format("H:i:s");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data['user_id'] = auth()->user()->id;
        if (isset($request['user_id'])) {
            $data['user_id'] = $request['user_id'];
        }
        if (isset($request['date'])) {
            $data['created_at'] = $request['date'];
        }

        $today = Carbon::today()->format('Y-m-d');
        $latest = CheckIn::where('user_id', $data['user_id'])->whereDate('created_at', $today)->orderBy('created_at', 'desc')->first();

        if (isset($latest["type"])) {
            if ($latest["type"] == "in") {
                $data['type'] = "out";
            } else {
                $data['type'] = "in";
            }
        } else {
            $data['type'] = "in";
        }

        $output["attendance"] = CheckIn::create($data);
        $output["time_logs"] = CheckIn::where('user_id', $data['user_id'])->whereDate('created_at', $today)->get();

        return $this->response('Attendance created successfully', $output, 200);
    }

    public function edit(string $id)
    {

        $checkin = CheckIn::findOrFail($id);
        return $this->response('Attendance listing',  $checkin, 200);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        if (isset($request->created_at)) {
            $createdAt = $request->created_at;
        } else {
            $createdAt = Carbon::today();
        }

        $checkins = CheckIn::whereUserId($id)->whereDate('created_at', $createdAt)->with('user');

        if ($request['perpage'] == 'All') {
            $data = $this->generalService->handleAllData($request, $checkins);
        } else {
            $data = $this->generalService->handlePagination($request, $checkins);
        }
        return $this->response('Attendance listing',  $data, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        CheckIn::whereId($id)->update([
            'created_at' => $request->date,
            'description' => $request->description,
        ]);
        return $this->response('Attendance updated successfully', true, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        if (CheckIn::whereId($id)->delete()) {
            return $this->response('Attendance deleted successfully', true, 204);
        }
    }


    public function fetchTimeLogs()
    {
        $timeLogs = CheckIn::whereUserId(auth()->id())->whereDate('created_at', Carbon::today())->get();
        return $this->response('Attendance updated successfully', $timeLogs, 200);
    }


    public function staffAttendance(Request $request)
    {
        $company_id = Auth::user()->company_id;
        $created_at = Carbon::today()->format('Y-m-d');

        if (isset($request->created_at)) {
            $created_at = $request->created_at;
        }

        $data = User::whereHas('today_time_logs', function ($query) use ($created_at) {
            $query->whereDate('created_at', $created_at);
        })
        ->with('today_time_logs')
        ->where('company_id', $company_id);
        
        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }
}
