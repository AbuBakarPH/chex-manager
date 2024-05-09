<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleAnswerRequest;
use App\Models\Schedule;
use App\Models\ScheduleAnswer;
use App\Services\MeidaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleAnswerController extends Controller
{
    public function __construct(private MeidaService $meidaService)
    {
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
    public function store(ScheduleAnswerRequest $request)
    {
        $data = $request->validated();
        $schedule = Schedule::with('configuration')->findOrFail($data["schedule_id"]);
        if ($schedule["status"] == "due") {
            Schedule::where('id', $data["schedule_id"])->update([
                'status' => 'in_progress'
            ]);
        }

        // $this->handleValidation($schedule);
        $data['user_id'] = Auth::id();
        $answer = ScheduleAnswer::create($data);

        if (isset($request["answer_type"]) && $request["answer_type"] == "file") {
            $this->meidaService->store($data["answer"], $answer["id"], "App\\Models\\ScheduleAnswer");
        }
        event(new \App\Events\AnswerEvent($answer));
        
        return $this->response('Answer submitted', $answer->load('photo', 'user.photo'), 200);
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
    public function update(ScheduleAnswerRequest $request, $id)
    {
        $data = $request->validated();
        $schedule = Schedule::findOrFail($data["schedule_id"]);
        // $this->handleValidation($schedule);
        $data['user_id'] = Auth::id();

        $answer = ScheduleAnswer::where('id', $id)->first();
        if (!$answer) {
            abort(422, 'Answer not found.');
        }
        $answer->update($data);

        if (isset($request["answer_type"]) && $request["answer_type"] == "file") {
            $this->meidaService->update($data["answer"], $answer["id"], "App\\Models\\ScheduleAnswer");
        }

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function handleValidation($schedule)
    {
        $today = Carbon::today()->format('Y-m-d');
        $created_at = Carbon::parse($schedule->created_at)->format('Y-m-d');

        if (isset($schedule["configuration"]["repeat"])) {
            $type = $schedule["configuration"]["repeat"];
            switch ($type) {
                case "weekly":
                    if (!boolval($created_at >= now()->subWeek())) {
                        abort(422, 'You can not submit answer to this schedule.');
                    }
                    break;
                case "monthly":
                    if (!boolval($created_at >= now()->subMonth())) {
                        abort(422, 'You can not submit answer to this schedule.');
                    }
                    break;
                case "yearly":
                    if (!boolval($created_at >= now()->subYear())) {
                        abort(422, 'You can not submit answer to this schedule.');
                    }
                    break;
                case "daily":
                    if (!boolval($created_at == date('Y-m-d'))) {
                        abort(422, 'You can not submit answer to this schedule.');
                    }
                    break;
            }
        } else {
            if (!boolval($created_at == $today)) {
                abort(422, 'You can not submit answer to this schedule.');
            }
        }
    }
}
