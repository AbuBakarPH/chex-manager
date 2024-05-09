<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TaskSectionRequest;
use App\Http\Requests\UpdateTaskSectionRequest;
use App\Models\TaskSection;
use App\Services\Interfaces\TaskSectionInterface;
use App\Services\TaskSectionService;

class TaskSectionController extends Controller
{
    public function __construct(private TaskSectionService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Sections listing', $this->service->index($request, null, 'checkListSectionQuestions'), 200);
    }

    public function store(TaskSectionRequest $request)
    {
        $section = $request->validated();
        $questions = $request->questions;
        return $this->response(
            'Section created successfully',
            $this->service->store($section, $questions),
            200
        );
    }

    public function show($id)
    {
        return $this->response(
            'Section data',
            $this->service->show($id),
            200
        );
    }

    public function update(TaskSectionRequest $request, $id)
    {
        $checkListSection = TaskSection::find($id);
        return $this->response('Section updated successfully', $this->service->update($request, $checkListSection), 200);
    }

    public function destroy($id)
    {
        $checkListSection = TaskSection::find($id);
        $this->service->destroy($checkListSection);
        return response()->noContent();
    }
}
