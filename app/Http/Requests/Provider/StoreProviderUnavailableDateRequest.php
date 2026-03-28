<?php

namespace App\Http\Requests\Provider;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProviderUnavailableDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (int) ($this->user()?->role ?? 0) === User::ROLE_PROVIDER;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'block_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time'],
            'reason' => ['nullable', 'string', 'max:255'],
            'reschedule_bookings' => ['nullable', 'boolean'],
            'reschedule_to_date' => ['nullable', 'date', 'after:block_date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $startTime = (string) $this->input('start_time', '');
            $endTime = (string) $this->input('end_time', '');

            if ($startTime !== '' || $endTime !== '') {
                if (strtotime($endTime) <= strtotime($startTime)) {
                    $validator->errors()->add('end_time', 'Block end time must be greater than block start time.');
                }
            }

            if ($this->boolean('reschedule_bookings') && !$this->filled('reschedule_to_date')) {
                $validator->errors()->add('reschedule_to_date', 'Please choose the new date for rescheduled appointments.');
            }
        });
    }
}
