<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolvedRiskRequest;
use App\Models\QuestionRisk;
use App\Services\MeidaService;
use App\Services\QuestionRiskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RiskController extends Controller
{
    public function __construct(private QuestionRiskService $service, private MeidaService $meidaService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dues = QuestionRisk::with(['question', 'assignees.photo', 'conversations.user.photo', 'conversations.photo'])
            ->where('status', 'in_progress')->where('due_date', '<', Carbon::today())
            ->whereHas('assignees', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })->get();

        $actives = QuestionRisk::with(['question', 'assignees.photo', 'conversations.user.photo', 'conversations.photo'])
            ->where('status', 'in_progress')->where('due_date', '>=', Carbon::today())
            ->whereHas('assignees', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })->get();

        return $this->response(
            'Risks fetched successfully',
            [
                'actives' => $actives,
                'dues' => $dues,
            ],
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];
        if ($authRole != 'Staff') {
            abort(403, 'Unauthorized: Only Staff users are allowed.');
        }

        $validationRules = [
            'description' => 'required',
            'section_question_id' => 'required|exists:section_questions,id',
            'image_id' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['status'] = 'draft';
        $data['assignees'] = [];
        $data['assignees'][] = auth()->user()->id;
        $risk = $this->service->createRisk($data);
        return $this->response(
            'Marked as risk',
            $risk->load(['assignees', 'conversations.photo', 'reason.user']),
            200
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
    public function update(Request $request)
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

    public function getHistory(Request $request)
    {
        $data = QuestionRisk::with(['question', 'assignees.photo', 'conversations.user.photo', 'conversations.photo'])
            ->where('status', 'completed')
            ->whereHas('assignees', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });

        if (isset($request->date_to) && $request->date_to) {
            $data = $data->whereDate('updated_at', '<=', $request->date_to);
        }

        if (isset($request->date_from) && $request->date_from) {
            $data = $data->whereDate('updated_at', '>=', $request->date_from);
        }

        $data = $data->get();

        return $this->response(
            'risks history fetched successfully',
            $data,
            200
        );
    }

    public function resolved(ResolvedRiskRequest $request)
    {
        $user = auth()->user();
        $authID = $user->id;
        $data = $request->validated();
        $risk = QuestionRisk::where('id', $data["risk_id"])->first();

        $risk->conversations()->create([
            'user_id' => $authID,
            'description' => $data["description"],
        ]);
        $risk = $risk->load('conversations');

        if ($data["image_id"]) {
            $this->meidaService->store($data["image_id"], $risk->conversations[count($risk->conversations) - 1]["id"], "App\\Models\\RiskConversation");
        }

        $risk->update(['status' => 'completed']);

        return response()->noContent();
    }
}
