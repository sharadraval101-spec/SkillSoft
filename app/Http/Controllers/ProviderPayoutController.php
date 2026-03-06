<?php

namespace App\Http\Controllers;

use App\Models\ProviderPayout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderPayoutController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();

        $payouts = ProviderPayout::query()
            ->with(['booking:id,booking_number,scheduled_at,status', 'commission:id,booking_id,platform_fee_percent'])
            ->where('provider_id', $provider->id)
            ->latest()
            ->paginate(12);

        return view('provider.payouts.index', compact('payouts'));
    }
}
