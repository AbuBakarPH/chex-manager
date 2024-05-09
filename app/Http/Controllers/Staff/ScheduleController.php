<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = Schedule::with([
            'answers.photo',
            'task.category',
            'configuration.assignees.photo',
            'task.sections.questions.risk',
            'task.sections.questions.fields',
            'task.sections.questions.sub_questions.fields',
        ])->whereHas('configuration.assignees', function ($q) {
            $q = $q->where('user_id', Auth::id());
        })->whereIn('status', ['in_progress', 'in_complete', 'draft'])->where('id', $id)->first();

        if (!$schedule) {
            abort(422, 'Schedule not found.');
        }

        return $this->response('Schedule', $schedule, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::where('company_id', auth()->user()->company_id)
            ->where('id', $id)->whereIn('status', ['in_progress', 'in_complete', 'draft'])->first();

        if (!$schedule) {
            abort(422, 'Schedule not found.');
        }
        
        $schedule->update(['status' => 'requested']);

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
