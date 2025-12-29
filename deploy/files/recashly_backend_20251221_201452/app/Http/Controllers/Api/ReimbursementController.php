<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReimbursementController extends Controller
{
    /**
     * Get user's reimbursements.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Reimbursement::with(['client:id,name', 'category:id,name,icon'])
            ->where('user_id', $request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by month
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('transaction_date', $request->month)
                  ->whereYear('transaction_date', $request->year);
        }

        // Search by client name or note
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', '%' . $search . '%');
                })->orWhere('note', 'like', '%' . $search . '%');
            });
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $reimbursements = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reimbursements,
        ]);
    }

    /**
     * Store a new reimbursement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'image' => 'required|image|max:20480', // 20MB max
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Find or create client
            $client = Client::firstOrCreate(
                ['name' => $validated['client_name']],
                [
                    'created_by' => $request->user()->id,
                    'is_auto_registered' => true,
                ]
            );

            // Store image
            $imagePath = $request->file('image')->store('receipts', 'public');

            // Create reimbursement
            $reimbursement = Reimbursement::create([
                'user_id' => $request->user()->id,
                'client_id' => $client->id,
                'category_id' => $validated['category_id'],
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'note' => $validated['note'] ?? null,
                'image_path' => $imagePath,
                'status' => Reimbursement::STATUS_PENDING,
            ]);

            $reimbursement->load(['client:id,name', 'category:id,name,icon']);

            return response()->json([
                'success' => true,
                'message' => 'Reimbursement berhasil disimpan',
                'data' => $reimbursement,
            ], 201);
        });
    }

    /**
     * Get reimbursement detail.
     */
    public function show(Request $request, Reimbursement $reimbursement): JsonResponse
    {
        // Ensure user owns this reimbursement
        if ($reimbursement->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan',
            ], 403);
        }

        $reimbursement->load(['client:id,name', 'category:id,name,icon', 'report']);

        return response()->json([
            'success' => true,
            'data' => $reimbursement,
        ]);
    }

    /**
     * Update a reimbursement.
     */
    public function update(Request $request, Reimbursement $reimbursement): JsonResponse
    {
        // Ensure user owns this reimbursement
        if ($reimbursement->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan',
            ], 403);
        }

        // Can only update pending reimbursements
        if ($reimbursement->status !== Reimbursement::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya entry pending yang bisa diubah',
            ], 422);
        }

        $validated = $request->validate([
            'client_name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'amount' => 'sometimes|numeric|min:0',
            'transaction_date' => 'sometimes|date',
            'note' => 'nullable|string|max:1000',
            'image' => 'sometimes|image|max:20480',
        ]);

        return DB::transaction(function () use ($request, $reimbursement, $validated) {
            // Update client if provided
            if (isset($validated['client_name'])) {
                $client = Client::firstOrCreate(
                    ['name' => $validated['client_name']],
                    [
                        'created_by' => $request->user()->id,
                        'is_auto_registered' => true,
                    ]
                );
                $reimbursement->client_id = $client->id;
            }

            // Update image if provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($reimbursement->image_path) {
                    Storage::disk('public')->delete($reimbursement->image_path);
                }
                $reimbursement->image_path = $request->file('image')->store('receipts', 'public');
            }

            // Update other fields
            if (isset($validated['category_id'])) {
                $reimbursement->category_id = $validated['category_id'];
            }
            if (isset($validated['amount'])) {
                $reimbursement->amount = $validated['amount'];
            }
            if (isset($validated['transaction_date'])) {
                $reimbursement->transaction_date = $validated['transaction_date'];
            }
            if (array_key_exists('note', $validated)) {
                $reimbursement->note = $validated['note'];
            }

            $reimbursement->save();
            $reimbursement->load(['client:id,name', 'category:id,name,icon']);

            return response()->json([
                'success' => true,
                'message' => 'Reimbursement berhasil diupdate',
                'data' => $reimbursement,
            ]);
        });
    }

    /**
     * Get dashboard summary.
     * Uses caching (5 minutes) to reduce database queries.
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $cacheKey = "user_{$userId}_pending_summary";

        // Cache for 5 minutes (300 seconds)
        $summaryData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($userId) {
            // Total pending amount
            $pendingTotal = Reimbursement::where('user_id', $userId)
                ->pending()
                ->sum('amount');

            // Pending count
            $pendingCount = Reimbursement::where('user_id', $userId)
                ->pending()
                ->count();

            // Pending per category
            $categoryPending = Reimbursement::where('user_id', $userId)
                ->pending()
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id')
                ->mapWithKeys(fn($value, $key) => [(string)$key => (float)$value])
                ->toArray();

            // All time total
            $allTimeTotal = Reimbursement::where('user_id', $userId)->sum('amount');

            return [
                'pending_total' => (float) $pendingTotal,
                'pending_count' => $pendingCount,
                'category_pending' => $categoryPending,
                'all_time_total' => (float) $allTimeTotal,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $summaryData,
        ]);
    }

    /**
     * Delete a reimbursement.
     * Only pending reimbursements can be deleted.
     */
    public function destroy(Request $request, Reimbursement $reimbursement): JsonResponse
    {
        // Ensure user owns this reimbursement
        if ($reimbursement->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan',
            ], 403);
        }

        // Can only delete pending reimbursements
        if ($reimbursement->status !== Reimbursement::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya entry pending yang bisa dihapus',
            ], 422);
        }

        // Delete associated image
        if ($reimbursement->image_path) {
            Storage::disk('public')->delete($reimbursement->image_path);
        }

        $reimbursement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reimbursement berhasil dihapus',
        ]);
    }
}
