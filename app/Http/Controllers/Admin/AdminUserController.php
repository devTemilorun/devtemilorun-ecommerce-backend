<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('role', 'customer')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));
            
        return UserResource::collection($users);
    }

    public function show($id)
    {
        $user = User::with('orders')->findOrFail($id);
        
        return new UserResource($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|in:admin,customer',
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return new UserResource($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Don't allow deleting last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() === 1) {
            return response()->json(['message' => 'Cannot delete the last admin user'], 400);
        }
        
        $user->delete();
        
        return response()->json(['message' => 'User deleted successfully']);
    }
}