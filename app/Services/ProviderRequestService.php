<?php

namespace App\Services;

use App\Mail\ProviderRequestApprovedMail;
use App\Mail\ProviderRequestRejectedMail;
use App\Models\ProviderProfile;
use App\Models\ProviderRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProviderRequestService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data): ProviderRequest
    {
        return DB::transaction(function () use ($data): ProviderRequest {
            $this->ensureEmailIsAvailable((string) $data['email']);

            $documents = collect($data['documents'] ?? [])
                ->filter(fn ($file) => $file instanceof UploadedFile)
                ->map(function (UploadedFile $file): array {
                    $path = $file->store('provider-requests', 'public');

                    return [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                    ];
                })
                ->values()
                ->all();

            /** @var ProviderRequest $providerRequest */
            $providerRequest = ProviderRequest::query()->create([
                'business_name' => $data['business_name'],
                'owner_name' => $data['owner_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'service_category_id' => $data['service_category_id'] ?? null,
                'business_details' => $data['business_details'],
                'address' => $data['address'],
                'documents' => $documents ?: null,
                'status' => ProviderRequest::STATUS_PENDING,
            ]);

            $this->auditLogService->log(
                'provider_request.submitted',
                $providerRequest,
                null,
                [],
                [
                    'email' => $providerRequest->email,
                    'business_name' => $providerRequest->business_name,
                    'service_category_id' => $providerRequest->service_category_id,
                ]
            );

            return $providerRequest->fresh(['serviceCategory']);
        });
    }

    public function approve(ProviderRequest $providerRequest, User $admin): ProviderRequest
    {
        return DB::transaction(function () use ($providerRequest, $admin): ProviderRequest {
            $providerRequest = ProviderRequest::query()
                ->lockForUpdate()
                ->findOrFail($providerRequest->id);

            if ($providerRequest->status !== ProviderRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'provider_request' => 'Only pending provider requests can be approved.',
                ]);
            }

            $this->ensureEmailIsAvailable($providerRequest->email, $providerRequest->id);

            $plainPassword = Str::password(16, true, true, true, false);

            $user = User::query()->create([
                'name' => $providerRequest->owner_name,
                'email' => $providerRequest->email,
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_PROVIDER,
                'is_active' => true,
            ]);
            $user->syncRoleFromLegacyValue();

            $providerProfile = ProviderProfile::query()->create([
                'user_id' => $user->id,
                'business_name' => $providerRequest->business_name,
                'bio' => $providerRequest->business_details,
                'status' => ProviderProfile::STATUS_ACTIVE,
                'availability_status' => ProviderProfile::AVAILABILITY_AVAILABLE,
                'verified_at' => now(),
            ]);

            $oldValues = [
                'status' => $providerRequest->status,
                'approved_user_id' => $providerRequest->approved_user_id,
                'provider_profile_id' => $providerRequest->provider_profile_id,
            ];

            $providerRequest->update([
                'status' => ProviderRequest::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'approved_user_id' => $user->id,
                'provider_profile_id' => $providerProfile->id,
                'review_notes' => null,
            ]);

            $this->auditLogService->log(
                'provider_request.approved',
                $providerRequest,
                $admin,
                $oldValues,
                [
                    'status' => $providerRequest->status,
                    'approved_user_id' => $user->id,
                    'provider_profile_id' => $providerProfile->id,
                ]
            );

            Mail::to($providerRequest->email)->queue(new ProviderRequestApprovedMail(
                $providerRequest,
                $user,
                $plainPassword,
                route('login')
            ));

            return $providerRequest->fresh([
                'serviceCategory',
                'reviewer',
                'approvedUser',
                'providerProfile',
            ]);
        });
    }

    public function reject(ProviderRequest $providerRequest, User $admin, ?string $reason = null): ProviderRequest
    {
        return DB::transaction(function () use ($providerRequest, $admin, $reason): ProviderRequest {
            $providerRequest = ProviderRequest::query()
                ->lockForUpdate()
                ->findOrFail($providerRequest->id);

            if ($providerRequest->status !== ProviderRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'provider_request' => 'Only pending provider requests can be rejected.',
                ]);
            }

            $oldValues = [
                'status' => $providerRequest->status,
                'review_notes' => $providerRequest->review_notes,
            ];

            $providerRequest->update([
                'status' => ProviderRequest::STATUS_REJECTED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_notes' => $reason,
            ]);

            $this->auditLogService->log(
                'provider_request.rejected',
                $providerRequest,
                $admin,
                $oldValues,
                [
                    'status' => $providerRequest->status,
                    'review_notes' => $providerRequest->review_notes,
                ]
            );

            Mail::to($providerRequest->email)->queue(new ProviderRequestRejectedMail(
                $providerRequest,
                $reason
            ));

            return $providerRequest->fresh([
                'serviceCategory',
                'reviewer',
            ]);
        });
    }

    private function ensureEmailIsAvailable(string $email, ?string $ignoreRequestId = null): void
    {
        $email = Str::lower(trim($email));

        $existingUser = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => 'A user account already exists with this email address.',
            ]);
        }

        $existingRequest = ProviderRequest::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('status', [
                ProviderRequest::STATUS_PENDING,
                ProviderRequest::STATUS_APPROVED,
            ])
            ->when($ignoreRequestId, fn ($query) => $query->whereKeyNot($ignoreRequestId))
            ->exists();

        if ($existingRequest) {
            throw ValidationException::withMessages([
                'email' => 'A provider request is already active for this email address.',
            ]);
        }
    }
}
