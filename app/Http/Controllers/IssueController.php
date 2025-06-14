<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(Issue::with('product')->paginate($request->query('per_page', 10)), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $issue = Issue::create($validator->validated());
        return response()->json($issue, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $issue = Issue::with('product')->find($id);
        return $issue ? response()->json($issue) : response()->json(['error' => 'Not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $issue = Issue::find($id);
        if (!$issue) return response()->json(['error' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'description' => 'string|max:1000',
            'status' => 'in:open,resolved',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $issue->update($validator->validated());
        return response()->json($issue);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $issue = Issue::find($id);
        if (!$issue) return response()->json(['error' => 'Not found'], 404);

        $issue->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
