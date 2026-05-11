<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceAssignment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeviceService
{
    public function index()
    {
        return Device::all();
    }

    public function assign(array $data)
    {
        $validator = Validator::make($data, [
            'device_id' => 'required|integer|exists:devices,id',
            'user_id' => 'required|integer|exists:users,id',
            'assigned_at' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $assignment = DeviceAssignment::create([
            'device_id' => $data['device_id'],
            'user_id' => $data['user_id'],
            'assigned_at' => $data['assigned_at'] ?? now()
        ]);

        return $assignment;
    }
}
