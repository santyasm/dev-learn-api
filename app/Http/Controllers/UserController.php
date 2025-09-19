<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateSelfUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Exception;

class UserController extends Controller
{
    public function index()
    {
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $users = User::all();
            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching users.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated.'], 401);
            }
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching authenticated user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($id);
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'User not found.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validated();
            $user = User::create($validatedData);
            return response()->json($user, 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $data = $request->validated();
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($id);
            $user->update($data);
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateSelf(UpdateSelfUserRequest $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated.'], 401);
            }

            $user->update($request->validated());
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroySelf()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated.'], 401);
            }

            $user->delete();

            return response()->json(['message' => 'Account deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
