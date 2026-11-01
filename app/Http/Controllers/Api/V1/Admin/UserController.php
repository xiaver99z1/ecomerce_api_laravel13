<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Simple ABAC check: only super admins or management can list users
        if (! $request->user()->is_super_admin && $request->user()->department !== 'management') {
            return response()->json(['message' => 'Unauthorized attributes.'], 403);
        }

        $users = User::paginate(10);

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    public function updateAttributes(Request $request, User $targetUser): JsonResponse
    {
        if (! $request->user()->is_super_admin) {
            return response()->json(['message' => 'Only super admins can modify user attributes/roles.'], 403);
        }

        $request->validate([
            'department' => ['required', 'string'],
            'store_id' => ['required', 'integer'],
            'is_super_admin' => ['required', 'boolean'],
        ]);

        $targetUser->update($request->only(['department', 'store_id', 'is_super_admin']));

        return response()->json([
            'message' => 'User attributes updated successfully',
            'user' => new UserResource($targetUser),
        ]);
    }
}
