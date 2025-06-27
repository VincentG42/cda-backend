<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserTypeController extends Controller
{
    public function index()
    {
        return response()->json(UserType::all());
    }

    public function show($id)
    {
        $userType = UserType::findOrFail($id);
        return response()->json($userType);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:user_types,name',
        ]);
        $userType = UserType::create($validated);
        return response()->json($userType, 201);
    }

    public function update(Request $request, $id)
    {
        $userType = UserType::findOrFail($id);
        $validated = $request->validate([
            'name' => ['string', Rule::unique('user_types')->ignore($userType->id)],
        ]);
        $userType->update($validated);
        return response()->json($userType);
    }

    public function destroy($id)
    {
        $userType = UserType::findOrFail($id);
        $userType->delete();
        return response()->json(['message' => 'Type utilisateur supprimé']);
    }
}
