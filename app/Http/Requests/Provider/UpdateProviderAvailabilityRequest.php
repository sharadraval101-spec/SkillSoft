<?php

namespace App\Http\Requests\Provider;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProviderAvailabilityRequest extends FormRequest
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
            'days' => ['required', 'array'],
            'days.*.is_active' => ['nullable', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i'],
            'days.*.break_start_time' => ['nullable', 'date_format:H:i'],
            'days.*.break_end_time' => ['nullable', 'date_format:H:i'],
            'days.*.slot_duration' => ['nullable', 'integer', 'in:15,30,45,60'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $days = $this->input('days', []);

            for ($day = 0; $day <= 6; $day++) {
                $dayData = $days[$day] ?? null;
                if (!is_array($dayData)) {
                    $validator->errors()->add('days', 'Weekly availability data is incomplete.');
                    continue;
                }

                $isActive = filter_var($dayData['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if (!$isActive) {
                    continue;
                }

                $startTime = $dayData['start_time'] ?? null;
                $endTime = $dayData['end_time'] ?? null;
                $slotDuration = $dayData['slot_duration'] ?? null;

                if (!$startTime || !$endTime) {
                    $validator->errors()->add("days.$day.start_time", 'Start and end times are required for active days.');
                    continue;
                }

                if (strtotime($endTime) <= strtotime($startTime)) {
                    $validator->errors()->add("days.$day.end_time", 'End time must be greater than start time.');
                }

                if (!$slotDuration) {
                    $validator->errors()->add("days.$day.slot_duration", 'Slot duration is required for active days.');
                }

                $breakStart = $dayData['break_start_time'] ?? null;
                $breakEnd = $dayData['break_end_time'] ?? null;

                if (!$breakStart && !$breakEnd) {
                    continue;
                }

                if (!$breakStart || !$breakEnd) {
                    $validator->errors()->add("days.$day.break_start_time", 'Both break start and break end are required when setting a break.');
                    continue;
                }

                if (strtotime($breakEnd) <= strtotime($breakStart)) {
                    $validator->errors()->add("days.$day.break_end_time", 'Break end time must be greater than break start time.');
                    continue;
                }

                if (strtotime($breakStart) <= strtotime($startTime) || strtotime($breakEnd) >= strtotime($endTime)) {
                    $validator->errors()->add("days.$day.break_start_time", 'Break time must be inside working hours.');
                }
            }
        });
    }
}

