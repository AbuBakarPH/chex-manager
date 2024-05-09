<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Models\Field;
use App\Services\FieldService;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function __construct(private FieldService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Category listing', $this->service->index($request), 200);
    }

    public function store(StoreFieldRequest $request)
    {
        $data = $request->validated();
        return $this->response(
            'Field created successfully',
            $this->service->store($data),
        );
    }

    public function show($id)
    {
        $field = Field::find($id);
        return $this->response('Field detail', $field);
    }

    public function update(StoreFieldRequest $request, $id)
    {
        $data  = $request->validated();
        return $this->response(
            'Field updated successfully',
            $this->service->update($data, $id)
        );
    }

    public function destroy($id)
    {
        $formField = Field::findOrFail($id);
        $formField->delete();
        return response()->noContent();
    }
}
