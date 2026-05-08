<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->all());
    }

    public function show($id)
    {
        return response()->json($this->service->find($id));
    }

    public function store(Request $request)
    {
        return response()->json(
            $this->service->create($request->all()),
            201
        );
    }

    public function update(Request $request, $id)
    {
        return response()->json(
            $this->service->update($id, $request->all())
        );
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true
        ]);
    }
}