<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\CqcVisit;
use Illuminate\Http\Request;
use App\Services\CqcVisitService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CqcVisitRequest;

class CqcVisitController extends Controller
{
    public function __construct(private CqcVisitService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->response('Cqc visit listing', $this->service->index($request), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CqcVisitRequest $request)
    {
        $isUpcomingCqcVisitExists = CqcVisit::where('company_id', auth()->user()->company_id)
            ->whereDate('visit_date', '>', Carbon::now())
            ->exists();
        if (!$isUpcomingCqcVisitExists) {
            return $this->response('Cqc visit created successfully', $this->service->store($request), 200);
        } else {
            return $this->response('Already have an upcoming Cqc visit', null, 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cqcVisit = CqcVisit::findOrFail($id);
        return $this->response('Cqc visit detail', $cqcVisit, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CqcVisitRequest $request, $id)
    {
        $cqcVisit = CqcVisit::findOrFail($id);
        if (!$cqcVisit) {
            return $this->response('Cqc visit not found', null, 404);
        }

        return $this->response('Cqc visit updated successfully', $this->service->update($request, $cqcVisit), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cqcVisit = CqcVisit::findOrFail($id);

        DB::beginTransaction();

        try {
            $cqcVisit->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
                'status' => $e->getCode() ?: 500,
            ], $e->getCode() ?: 500);
        }

        return $this->response('Cqc visit deleted', $cqcVisit, 200);
        // return response()->noContent();
    }
}
