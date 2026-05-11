<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Device;

class FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_api_flow()
    {
        // Enable debug for debug notify endpoint
        config(['app.debug' => true]);

        // Register
        $registerPayload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ];

        $registerRes = $this->postJson('/api/register', $registerPayload);
        $registerRes->assertStatus(201);
        $this->assertArrayHasKey('data', $registerRes->json());
        $token = data_get($registerRes->json(), 'data.token');
        $this->assertNotEmpty($token);

        // Create a device
        $device = Device::create(['name' => 'Laptop A', 'serial_number' => 'SN123', 'type' => 'laptop']);

        // Create ticket
        $ticketPayload = [
            'title' => 'Battery issue',
            'description' => 'Does not charge',
            'device_id' => $device->id,
            'priority' => 'high'
        ];

        $ticketRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/tickets', $ticketPayload);

        $ticketRes->assertStatus(201);
        $this->assertArrayHasKey('data', $ticketRes->json());

        // Assign device
        $assignRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/devices/assign', ['device_id' => $device->id, 'user_id' => data_get($registerRes->json(), 'data.user.id')]);

        $assignRes->assertStatus(201);
        $this->assertArrayHasKey('data', $assignRes->json());

        // Logout
        $logoutRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutRes->assertStatus(200);

        // Debug notify
        $debugRes = $this->postJson('/api/debug/notify', ['message' => 'integration test']);
        $debugRes->assertStatus(200);
        $this->assertArrayHasKey('data', $debugRes->json());
    }
}
