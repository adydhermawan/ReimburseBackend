<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Get all clients for autocomplete.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        // Search filter for autocomplete
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $clients = $query->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * Create a new client (auto-register from mobile).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:clients,name',
        ]);

        $client = Client::create([
            'name' => $validated['name'],
            'created_by' => $request->user()->id,
            'is_auto_registered' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client berhasil ditambahkan',
            'data' => $client,
        ], 201);
    }
}
