<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderBookingController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $provider */
        $provider = $request->user();

        $bookings = Booking::query()
            ->with([
                'customer:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ])
            ->where('provider_id', $provider->id)
            ->latest('scheduled_at')
            ->paginate(12);

        return view('provider.bookings.index', [
            'bookings' => $bookings,
        ]);
    }
}

