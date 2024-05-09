<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectionQuestionRequest;
use App\Models\SectionQuestion;
use App\Services\SectionQuestionService;
use Illuminate\Http\Request;

class SectionQuestionController extends Controller
{
    public function __construct(private SectionQuestionService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Questions listing', $this->service->index($request, null, 'questionFormFields'), 200);
    }

    public function store(SectionQuestionRequest $request)
    {
        $data = $request->validated();
        $question = $this->service->store($data);
        $this->service->handleFields($question->id, $request->fields);
        $this->service->handleSubQuestions($question->id, $request->sub_questions);

        return $this->response(
            'Question created successfully',
            $question->load(['fields', 'sub_questions']),
            200
        );
    }

    public function show($id)
    {
        return $this->response(
            'Question',
            $this->service->show($id),
            200
        );
    }

    public function update(SectionQuestionRequest $request, $id)
    {
        $data = $request->validated();
        $question = SectionQuestion::find($id);
        $question->update($data);
        $this->service->handleFields($id, $request->fields);
        $this->service->handleSubQuestions($id, $request->sub_questions);

        return $this->response(
            'Question updated successfully',
            $question->load(['fields', 'sub_questions']),
            200
        );
    }

    public function destroy($id)
    {
        $checkListSectionQues = SectionQuestion::find($id);
        $this->service->destroy($checkListSectionQues);
        return response()->noContent();
    }
}
