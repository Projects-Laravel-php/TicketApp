<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    protected $service;

    public function __construct(DeviceService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->index()
        ]);
    }

    public function assign(Request $request)
    {
        $assignment = $this->service->assign($request->all());

        return response()->json([
            'success' => true,
            'data' => $assignment
        ], 201);
    }
}