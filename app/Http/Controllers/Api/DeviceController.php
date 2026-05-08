<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceAssignment;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        return response()->json(Device::all());
    }

    public function assign(Request $request)
    {
        $assignment = DeviceAssignment::create([
            'device_id' => $request->device_id,
            'user_id' => $request->user_id,
            'assigned_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $assignment
        ]);
    }
}