<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Device::create([
            'name' => 'Dell Latitude',
            'serial_number' => 'DL-001',
            'type' => 'Laptop'
        ]);
    }
}