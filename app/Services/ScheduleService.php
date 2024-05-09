<?php

namespace App\Services;

use App\Models\Schedule;
use App\Services\Interfaces\ScheduleInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Class ScheduleService
 * @package App\Services
 */
class ScheduleService implements ScheduleInterface
{

    public function __construct(private Schedule $model, private GeneralService $generalService)
    {
        // parent::__construct($model);
    }

    public function index($request)
    {
        $relationalCols = [
            'task' => ['name'],
        ];

        $data = Schedule::where('company_id', auth()->user()->company->id)->with(['task', 'configuration', 'task.mythBusters']);

        if (isset($request->is_audit)) {

            $data =  $data->whereNotIn('status', ['verified']);
        };

        if (isset($request->name)) {
            $data =  $data->whereHas('task', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->name}%");
            });
        };

        if (isset($request->priority)) {
            $data =  $data->whereHas('task', function ($q) use ($request) {
                $q->where('priority', 'LIKE', "%{$request->priority}%");
            });
        };

        if (isset($request->status)) {

            $data =  $data->where('status', $request->status);
        };
        if (isset($request->created_at)) {
            $data =  $data->whereDate('created_at', $request->created_at);
        };

        if (isset($request->repeat)) {
            $data = $data->whereHas('configuration', function ($q) use ($request) {
                $q->where('repeat', $request->repeat);
            });
        };

        $data = $this->generalService->handleSearch($request['searchText'], $data, [], '', $relationalCols);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function approversScheduleList($request)
    {
        $relationalCols = [
            'task' => ['name'],
        ];

        $data = Schedule::where('company_id', auth()->user()->company_id)->with(['task', 'configuration.approvers', 'task.mythBusters']);
        $data = $data->whereHas('configuration.approvers', function ($q) {
            $q = $q->where('user_id', Auth::id());
        });

        if (isset($request->is_audit)) {

            $data =  $data->whereNotIn('status', ['verified']);
        };

        if (isset($request->name)) {
            $data =  $data->whereHas('task', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->name}%");
            });
        };

        if (isset($request->priority)) {
            $data =  $data->whereHas('task', function ($q) use ($request) {
                $q->where('priority', 'LIKE', "%{$request->priority}%");
            });
        };

        if (isset($request->status)) {

            $data =  $data->where('status', $request->status);
        };
        if (isset($request->created_at)) {
            $data =  $data->whereDate('created_at', $request->created_at);
        };

        if (isset($request->repeat)) {
            $data = $data->whereHas('configuration', function ($q) use ($request) {
                $q->where('repeat', $request->repeat);
            });
        };

        $data = $this->generalService->handleSearch($request['searchText'], $data, [], '', $relationalCols);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }
}
