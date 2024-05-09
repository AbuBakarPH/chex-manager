<?php

namespace App\Http\Controllers\ManagerControllers;

use Illuminate\Http\Request;
use App\Models\Manager\Employee;
use App\Services\GeneralService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Manager\EmployeeRequest;
use App\Services\MeidaService;

class EmployeeController extends Controller
{
    public function __construct(private GeneralService $generalService, private MeidaService $meidaService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchCols = ['name', 'email', 'phone', 'address'];
        $data = Employee::where('company_id', auth()->user()->company_id)->with('photo');

        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', []);
        if ($request['perpage'] == 'all') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();
        $data["company_id"] = Auth::user()->company_id;

        $employee = Employee::create($data);
        $this->meidaService->store($data["image_id"], $employee["id"], get_class($employee));

        return response(
            $employee->load('photo'),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employee = Employee::with('photo')->find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        return response(
            $employee,
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $data = $request->validated();
        $employee->update($data);
        if (isset($data["image_id"])) {
            $this->meidaService->update($data["image_id"], $employee["id"], get_class($employee));
        }
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $employee->delete();
        return response()->noContent();
    }
}
