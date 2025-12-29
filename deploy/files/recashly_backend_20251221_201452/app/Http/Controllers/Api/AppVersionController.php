<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AppVersionController extends Controller
{
    /**
     * Get the latest app version info.
     * This endpoint is public so the app can check for updates without authentication.
     */
    public function index(): JsonResponse
    {
        $version = Cache::remember('latest_app_version', 3600, function () {
            return AppVersion::getLatest();
        });

        if (!$version) {
            return response()->json([
                'success' => false,
                'message' => 'No version available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'version' => $version->version,
                'build_number' => $version->build_number,
                'download_url' => $version->download_url,
                'release_notes' => $version->release_notes,
                'is_mandatory' => $version->is_mandatory,
            ],
        ]);
    }

    /**
     * Store a new app version.
     * Only accessible by admin users.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'version' => 'required|string|max:20',
            'build_number' => 'required|integer|min:1',
            'download_url' => 'required|url',
            'release_notes' => 'nullable|string',
            'is_mandatory' => 'boolean',
        ]);

        $version = AppVersion::create([
            'version' => $validated['version'],
            'build_number' => $validated['build_number'],
            'download_url' => $validated['download_url'],
            'release_notes' => $validated['release_notes'] ?? null,
            'is_mandatory' => $validated['is_mandatory'] ?? false,
            'is_active' => true,
        ]);

        // Set as active and clear cache
        $version->setAsActive();
        Cache::forget('latest_app_version');

        return response()->json([
            'success' => true,
            'message' => 'App version created successfully',
            'data' => $version,
        ], 201);
    }
}
