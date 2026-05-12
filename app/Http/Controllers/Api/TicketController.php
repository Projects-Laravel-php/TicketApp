<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketController extends Controller
{
    protected $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->respond(function () {
            return $this->service->all(auth()->id());
        });
    }

    public function show($id)
    {
        return $this->respond(function () use ($id) {
            return $this->service->find($id, auth()->id());
        });
    }

    public function store(Request $request)
    {
        return $this->respond(function () use ($request) {
            $ticket = $this->service->create($request->all());

            return response()->json(['success' => true, 'data' => $ticket], 201);
        });
    }

    public function update(Request $request, $id)
    {
        return $this->respond(function () use ($request, $id) {
            return $this->service->update($id, $request->all(), auth()->id());
        });
    }

    public function destroy($id)
    {
        return $this->respond(function () use ($id) {
            $this->service->delete($id, auth()->id());

            return response()->json(['success' => true], 204);
        });
    }

    protected function respond(callable $callback)
    {
        try {
            $result = $callback();

            return $result instanceof \Illuminate\Http\JsonResponse
                ? $result
                : response()->json(['success' => true, 'data' => $result]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Validation failed',
                    'errors' => $exception->errors(),
                ],
            ], 422);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'success' => false,
                'error' => ['message' => $exception->getMessage() ?: 'No estás autorizado'],
            ], 403);
        } catch (ModelNotFoundException | NotFoundHttpException $exception) {
            return response()->json([
                'success' => false,
                'error' => ['message' => $exception->getMessage() ?: 'Recurso no encontrado'],
            ], 404);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'error' => ['message' => $exception->getMessage() ?: 'Error del servidor'],
            ], method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500);
        }
    }
}
