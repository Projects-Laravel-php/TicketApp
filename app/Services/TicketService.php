<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function all($userId = null)
    {
        $query = Ticket::with('user');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function find($id, $userId = null)
    {
        $ticket = Ticket::with('user')->find($id);

        if (! $ticket) {
            throw (new ModelNotFoundException('Ticket no encontrado'))->setModel(Ticket::class);
        }

        if ($userId !== null && $ticket->user_id !== $userId) {
            throw new AuthorizationException('No estás autorizado para ver este ticket.');
        }

        return $ticket;
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

        unset($data['user_id']);
        $data['user_id'] = Auth::id();

        return Ticket::create($data);
    }

    public function update($id, array $data, $userId = null)
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

        $ticket = $this->find($id, $userId);
        $ticket->update($data);

        return $ticket;
    }

    public function delete($id, $userId = null)
    {
        $ticket = $this->find($id, $userId);

        return $ticket->delete();
    }
}
