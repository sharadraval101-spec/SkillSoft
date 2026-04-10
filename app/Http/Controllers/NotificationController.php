<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->count();

        $view = (int) $user->role === User::ROLE_CUSTOMER
            ? 'customer.notifications.index'
            : 'notifications.index';

        return view($view, compact('notifications', 'unreadCount'));
    }

    public function read(Request $request): RedirectResponse
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
        } elseif ($request->filled('notification_id')) {
            $query->whereKey($request->string('notification_id')->toString())->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notification(s) marked as read.');
    }
}
