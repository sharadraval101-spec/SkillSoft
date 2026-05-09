<?php

namespace App\Http\Requests\Provider;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderOperationalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array((int) ($this->user()?->role ?? 0), [
            User::ROLE_PROVIDER,
            User::ROLE_ADMIN,
        ], true);
    }

    public function rules(): array
    {
        return [
            'availability_status' => 'required|in:'.implode(',', [
                ProviderProfile::AVAILABILITY_AVAILABLE,
                ProviderProfile::AVAILABILITY_UNAVAILABLE,
            ]),
            'unavailable_from' => 'nullable|date',
            'unavailable_until' => 'nullable|date|after_or_equal:unavailable_from',
            'unavailability_reason' => 'nullable|string|max:255',
        ];
    }
}
