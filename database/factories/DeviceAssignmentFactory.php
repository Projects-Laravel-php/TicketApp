<?php

namespace Database\Factories;

use App\Models\DeviceAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DeviceAssignment>
 */
class DeviceAssignmentFactory extends Factory
{
    protected $model = DeviceAssignment::class;

    public function definition(): array
    {
        return [
            'assigned_at' => fake()->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
