<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'device_id' => 'nullable|integer|exists:devices,id',
            'priority' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Ticket::create($data);
    }

    public function update($id, array $data)
    {
        $validator = Validator::make($data, [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string',
            'assigned_to' => 'sometimes|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);

        return $ticket;
    }

    public function delete($id)
    {
        return Ticket::destroy($id);
    }
}