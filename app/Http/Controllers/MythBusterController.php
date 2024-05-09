<?php

namespace App\Http\Controllers;

use App\Models\MythBuster;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateMythBusterRequest;
use App\Services\MeidaService;
use App\Services\MythBusterService;
use App\Services\Interfaces\MythBusterInterface;

class MythBusterController extends Controller
{
    public function __construct(private MeidaService $meidaService, private MythBusterService $service)
    {
        // parent::__construct($model);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->response('Myth Buster listing', $this->service->index($request), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->response(
            'MythBuster created successfully',
            $this->service->store($request),
            200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mythBuster = MythBuster::with('document')->where('id', $id)->first();
        return $this->response('MythBuster detail', $mythBuster, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMythBusterRequest $request,$id)
    {
        $mythBuster = MythBuster::where('id', $id)->first();

        return $this->response('MythBuster updated successfully', $this->service->update($request->validated(), $mythBuster), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
