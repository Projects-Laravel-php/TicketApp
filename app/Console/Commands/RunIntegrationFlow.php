<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuthService;
use App\Services\TicketService;
use App\Services\DeviceService;
use App\Services\DiscordWebhookService;
use App\Models\Device;

class RunIntegrationFlow extends Command
{
    protected $signature = 'run:integration-flow';
    protected $description = 'Run integration flow: register, login, create ticket, assign device, logout, notify';

    public function handle(AuthService $authService, TicketService $ticketService, DeviceService $deviceService)
    {
        $this->info('Starting integration flow...');

        try {
            $this->info('Registering user...');
            $email = 'integration+' . time() . '@example.com';
            $reg = $authService->register([
                'name' => 'Integration User',
                'email' => $email,
                'password' => 'secret123',
                'password_confirmation' => 'secret123'
            ]);

            $this->info('User created: ' . $reg['user']->email);
            $token = $reg['token'];

            $this->info('Creating device...');
            $device = Device::create(['name' => 'Integration Device', 'serial_number' => 'INT123', 'type' => 'laptop']);

            $this->info('Creating ticket...');
            $ticket = $ticketService->create([
                'title' => 'Integration ticket',
                'description' => 'Automated test',
                'device_id' => $device->id,
                'priority' => 'low',
                'user_id' => $reg['user']->id,
            ]);

            $this->info('Assigning device...');
            $assignment = $deviceService->assign(['device_id' => $device->id, 'user_id' => $reg['user']->id]);

            $this->info('Logging out...');
            $authService->logout($reg['user']);

            $this->info('Sending debug notify...');
            $discord = DiscordWebhookService::send(['type' => 'INTEGRATION', 'message' => 'Integration flow executed']);

            $this->info('Integration flow completed. Results:');
            $this->line('Token: ' . substr($token, 0, 12) . '...');
            $this->line('Ticket ID: ' . $ticket->id);
            $this->line('Assignment ID: ' . $assignment->id);
            $this->line('Discord notified: ' . ($discord ? 'yes' : 'no'));

            return 0;
        } catch (\Throwable $e) {
            $this->error('Integration flow failed: ' . $e->getMessage());
            return 1;
        }
    }
}
