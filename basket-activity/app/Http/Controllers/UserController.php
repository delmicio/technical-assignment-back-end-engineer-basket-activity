<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Get a list of all users.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::all(['id', 'name']);

        return response()->json($users);
    }
}
