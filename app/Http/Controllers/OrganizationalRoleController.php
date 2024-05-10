<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationalRoleRequest;
use App\Models\OrganizationalRole;
use App\Services\Interfaces\OrganizationalRoleInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class OrganizationalRoleController extends Controller
{
    public function __construct(private OrganizationalRoleInterface $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response([
            'message' => 'Data retrieved successfully',
            'data' => $this->service->index($request),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrganizationalRoleRequest $request)
    {
        $validated = $request->validated();
        $data = OrganizationalRole::create($validated);
        return response([
            'message' => 'Role created successfully',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $organizationalRole = OrganizationalRole::findOrFail($id);
            return response([
                'message' => 'Role retrieved successfully',
                'data' => $organizationalRole,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Role not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrganizationalRoleRequest $request, string $id)
    {
        try {
            $organizationalRole = OrganizationalRole::findOrFail($id);
            $validated = $request->validated();
            $organizationalRole->update($validated);
            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Role not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $organizationalRole = OrganizationalRole::findOrFail($id);
            $organizationalRole->delete();
            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Role not found',
            ], 404);
        }
    }
}
