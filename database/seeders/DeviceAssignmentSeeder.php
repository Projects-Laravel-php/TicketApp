<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Models\DeviceAssignment;

class DeviceAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $deviceIds = DB::table('devices')->pluck('id')->toArray();

        if (empty($userIds) || empty($deviceIds)) {
            $this->command->info('Skipping DeviceAssignmentSeeder: missing users or devices.');
            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            DeviceAssignment::factory()->create([
                'device_id' => Arr::random($deviceIds),
                'user_id' => Arr::random($userIds),
            ]);
        }
    }
}
