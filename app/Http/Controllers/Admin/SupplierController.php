<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Models\User;
use App\Services\Admin\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response($this->service->index($request), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $data = $request->validated();
        return response(
            $this->service->store($data),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = User::with(['photo', 'roles'])->where('id', $id)->first();
        $role = $supplier["roles"][0]["name"];
        if (!$supplier && $role != "Supplier") {
            abort(402, "Supplier not found");
        }

        return response($supplier, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, string $id)
    {
        $supplier = User::with(['photo', 'roles'])->where('id', $id)->first();
        $role = $supplier["roles"][0]["name"];
        if (!$supplier && $role != "Supplier") {
            abort(402, "Supplier not found");
        }
        
        $data = $request->validated();
        $data = collect($data)->except(['email'])->all();
        $this->service->update($data, $supplier);
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        User::whereId($id)->delete();
        return response()->noContent();
    }
}
