<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    public function all()
    {
        return Ticket::with('user')->get();
    }

    public function find($id)
    {
        return Ticket::findOrFail($id);
    }

    public function create(array $data)
    {
        return Ticket::create($data);
    }

    public function update($id, array $data)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);

        return $ticket;
    }

    public function delete($id)
    {
        return Ticket::destroy($id);
    }
}