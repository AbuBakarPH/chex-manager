<?php

namespace App\Services;

use App\Models\MythBuster;
use App\Services\MeidaService;
use App\Services\GeneralService;

/**
 * Class MythBusterService
 * @package App\Services
 */
class MythBusterService
{
    public function __construct(private MythBuster $model, private MeidaService $meidaService, private GeneralService $generalService)
    {
        // parent::__construct($model);
        $this->generalService = $generalService;
    }


    public function index($request)
    {

        // $data = MythBuster::with('document', 'tasks');
        $data = MythBuster::select('id', 'title', 'description');
        if ($request['title']) {
            $data = $data->where('title', $request['title']); 
        }
        
        $data = $this->generalService->handleSearch($request['searchText'], $data, ['title']);
        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($request)
    {
        $mythBuster = MythBuster::create($request->all());
        $this->meidaService->store($request->image_id, $mythBuster->id, "App\\Models\\MythBuster");
        return $mythBuster;
    }


    public function update($request, $model)
    {
        $model->update($request);
        return $model->refresh();
    }

}
