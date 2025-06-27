<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'user_type_id' => 'required|exists:user_types,id',
            'lastname' => 'required',
            'firstname' => 'required',
            'licence_number' => 'nullable',
            'has_to_change_password' => 'boolean',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'email' => ['email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:6',
            'user_type_id' => 'exists:user_types,id',
            'lastname' => 'sometimes|required',
            'firstname' => 'sometimes|required',
            'licence_number' => 'nullable',
            'has_to_change_password' => 'boolean',
        ]);
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);
        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->userType && strtolower($user->userType->name) === 'admin') {
            return response()->json(['message' => 'Impossible de supprimer un administrateur.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
