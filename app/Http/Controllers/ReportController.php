<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use App\Models\Issue;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(Report::with(['issue', 'user'])->paginate($request->query('per_page', 10)), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'issue_id' => 'required|exists:issues,id',
            'details' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $report = Report::create([
            'issue_id' => $request->issue_id,
            'details' => $request->details,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($report, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $report = Report::with(['issue', 'user'])->find($id);
        return $report ? response()->json($report) : response()->json(['error' => 'Not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) return response()->json(['error' => 'Not found'], 404);

        if ($request->user()->id !== $report->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'details' => 'string|max:1000',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $report->update($validator->validated());
        return response()->json($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) return response()->json(['error' => 'Not found'], 404);

        if ($request->user()->id !== $report->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
