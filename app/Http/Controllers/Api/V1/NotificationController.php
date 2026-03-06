<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => Notification::query()
                    ->where('user_id', $user->id)
                    ->unread()
                    ->count(),
            ],
        ]);
    }

    public function read(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'notification_ids' => 'nullable|array',
            'notification_ids.*' => 'uuid',
            'all' => 'nullable|boolean',
        ]);

        $query = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at');

        if (!empty($data['all'])) {
            $query->update(['read_at' => now()]);
        } elseif (!empty($data['notification_ids'])) {
            $query->whereIn('id', $data['notification_ids'])->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notification(s) marked as read.',
        ]);
    }
}
