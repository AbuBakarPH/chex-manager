<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RiskConversation;
use Illuminate\Http\Request;

class RiskConversationController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'question_risk_id' => 'required|exists:question_risks,id',
            'description' => 'required|string|max:1000',
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
            'Message created successfully',
            $data->load('user.photo'),
            200
        );
    }
}
