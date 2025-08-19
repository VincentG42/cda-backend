<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Team::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'season_id' => 'required|exists:seasons,id',
            'coach_id' => 'nullable|exists:users,id',
            'gender' => 'required|string|in:male,female,mixed',
        ]);

        $team = Team::create($validatedData);

        return response()->json($team, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return response()->json($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $validatedData = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'season_id' => 'sometimes|required|exists:seasons,id',
            'coach_id' => 'nullable|exists:users,id',
            'gender' => 'sometimes|required|string|in:male,female,mixed',
        ]);

        $team->update($validatedData);

        return response()->json($team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(null, 204);
    }
}
