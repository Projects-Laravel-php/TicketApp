<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        $types = ['Laptop', 'Desktop', 'Tablet', 'Phone', 'Printer', 'Monitor'];

        return [
            'name' => fake()->company() . ' ' . fake()->word(),
            'serial_number' => 'SN-' . fake()->unique()->numerify('###') . '-' . fake()->bothify('????'),
            'type' => fake()->randomElement($types),
        ];
    }
}
