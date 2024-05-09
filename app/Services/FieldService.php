<?php

namespace App\Services;

use App\Models\Field;

/**
 * Class FieldService
 * @package App\Services
 */
class FieldService
{
    public function __construct(private Field $model, private GeneralService $generalService)
    {
    }

    public function index($request)
    {
        $data = Field::query();

        if (isset($request['types']) && count($request['types'])) {
            $data = $data->whereIn('name', $request['types']);
        }

        $data = $this->generalService->handleSearch($request['searchText'], $data, ['name', 'value', 'label', 'placeholder', 'type']);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($data)
    {
        $field = Field::create($data);
        return $field;
    }


    public function update($data, $id)
    {
        $field = Field::findOrFail($id);
        $field->update($data);

        return $field->refresh();
    }
}
