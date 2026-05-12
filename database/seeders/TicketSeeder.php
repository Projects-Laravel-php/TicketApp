<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Models\Ticket;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->info('Skipping TicketSeeder: no users found.');
            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            Ticket::factory()->create([
                'user_id' => Arr::random($userIds),
            ]);
        }
    }
}
