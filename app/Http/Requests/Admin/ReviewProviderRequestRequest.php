<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ReviewProviderRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (int) ($this->user()?->role ?? 0) === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
