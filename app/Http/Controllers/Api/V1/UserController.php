<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 20));
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 20;

        $users = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->findOrFail($id);

        return response()->json($user);
    }
}
