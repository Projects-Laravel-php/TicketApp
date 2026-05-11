<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function register(Request $request)
    {
        $result = $this->service->register($request->all());

        return response()->json([
            'success' => true,
            'data' => $result
        ], 201);
    }

    public function login(Request $request)
    {
        $result = $this->service->login($request->all());

        if (!$result) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Invalid credentials']
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    public function logout(Request $request)
    {
        $ok = $this->service->logout($request->user());

        if ($ok) {
            return response()->json(['success' => true, 'data' => ['message' => 'Logged out']]);
        }

        return response()->json(['success' => false, 'error' => ['message' => 'Not authenticated']], 401);
    }
}