<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\RiskConversation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiskConversationController extends Controller
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
        $validatedData = $request->validate([
            // 'question_risk_id' => 'required|exists:question_risks,id',
            'description' => 'required|string|max:1000',
            'question_risk_id' => [
                'required',
                Rule::exists('question_risks', 'id')->where(function ($query) {
                    $query->whereIn('status', ['draft', 'in_progress']);
                }),
            ],
        ], [
            'question_risk_id.required' => 'Risk ID is required.',
            'question_risk_id.exists' => 'Risk ID does not exist in the risks table.',
            'description.required' => 'Description is required.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);
        $validatedData["user_id"] = auth()->user()->id;

        $data = RiskConversation::create($validatedData);
        event(new \App\Events\RiskConversationEvent($data->load('user.photo')));
        
        return $this->response(
            'Message posted successfully',
            $data->load('user.photo'),
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
